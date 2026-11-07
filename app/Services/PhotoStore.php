<?php

namespace App\Services;

use GdImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Saves a site photo to the private disk. Every upload is decoded and
 * re-encoded as JPEG: that shrinks phone photos, turns them upright, and drops
 * EXIF data (including GPS) and anything else hidden in the original file.
 */
class PhotoStore
{
    public const DISK = 'local';

    private const MAX_SIDE = 1920;

    private const THUMB_SIDE = 480;

    private const QUALITY = 82;

    /**
     * @return array{path: string, thumb_path: string, size: int, width: int, height: int, taken_at: ?Carbon}
     */
    public function save(UploadedFile $file, string $directory): array
    {
        $image = $this->decode($file);
        $name = Str::uuid()->toString();

        $full = $this->resize($image, self::MAX_SIDE);
        $fullBytes = $this->jpeg($full);
        $thumbBytes = $this->jpeg($this->resize($image, self::THUMB_SIDE));

        $disk = Storage::disk(self::DISK);
        $disk->put($path = "{$directory}/{$name}.jpg", $fullBytes);
        $disk->put($thumbPath = "{$directory}/{$name}-thumb.jpg", $thumbBytes);

        return [
            'path' => $path,
            'thumb_path' => $thumbPath,
            'size' => strlen($fullBytes),
            'width' => imagesx($full),
            'height' => imagesy($full),
            'taken_at' => $this->takenAt($file),
        ];
    }

    private function decode(UploadedFile $file): GdImage
    {
        $image = @imagecreatefromstring((string) file_get_contents($file->getRealPath()));

        if (! $image instanceof GdImage) {
            throw ValidationException::withMessages(['photo' => 'This photo could not be read. Use a JPG, PNG, or WebP image.']);
        }

        return $this->applyOrientation($image, $file);
    }

    /** Rotate phone photos the way the camera meant them to be seen. */
    private function applyOrientation(GdImage $image, UploadedFile $file): GdImage
    {
        $orientation = (int) ($this->exif($file)['Orientation'] ?? 1);

        $rotated = match ($orientation) {
            3 => imagerotate($image, 180, 0),
            6 => imagerotate($image, -90, 0),
            8 => imagerotate($image, 90, 0),
            default => $image,
        };

        return $rotated instanceof GdImage ? $rotated : $image;
    }

    private function takenAt(UploadedFile $file): ?Carbon
    {
        $value = $this->exif($file)['DateTimeOriginal'] ?? null;
        if (! is_string($value)) {
            return null;
        }

        try {
            $taken = Carbon::createFromFormat('Y:m:d H:i:s', $value);
        } catch (\Throwable) {
            return null;
        }

        return $taken && $taken->isPast() ? $taken : null;
    }

    /**
     * @return array<string, mixed>
     */
    private function exif(UploadedFile $file): array
    {
        if (! function_exists('exif_read_data') || ! in_array($file->getMimeType(), ['image/jpeg', 'image/jpg'], true)) {
            return [];
        }

        $data = @exif_read_data($file->getRealPath());

        return is_array($data) ? $data : [];
    }

    private function resize(GdImage $image, int $maxSide): GdImage
    {
        $width = imagesx($image);
        $height = imagesy($image);
        $scale = min(1, $maxSide / max($width, $height));

        $newWidth = max(1, (int) round($width * $scale));
        $newHeight = max(1, (int) round($height * $scale));

        $canvas = imagecreatetruecolor($newWidth, $newHeight);
        // White behind transparent PNG/WebP areas, since JPEG has no alpha.
        imagefill($canvas, 0, 0, imagecolorallocate($canvas, 255, 255, 255));
        imagecopyresampled($canvas, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        return $canvas;
    }

    private function jpeg(GdImage $image): string
    {
        ob_start();
        imagejpeg($image, null, self::QUALITY);

        return (string) ob_get_clean();
    }
}
