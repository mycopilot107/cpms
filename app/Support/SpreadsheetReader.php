<?php

namespace App\Support;

use RuntimeException;
use SimpleXMLElement;
use ZipArchive;

/**
 * Reads the cell values of an .xlsx or .csv file into plain rows of strings,
 * without a spreadsheet library. Formulas come back as their last calculated
 * value, which Excel stores in the file.
 */
class SpreadsheetReader
{
    /** Uncompressed size cap per XML part, to refuse zip bombs. */
    private const MAX_PART_BYTES = 40 * 1024 * 1024;

    private const MAX_ROWS = 5000;

    private const NS_MAIN = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';

    private const NS_REL = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships';

    /**
     * Sheet names in workbook order (a CSV has one sheet called "Sheet1").
     *
     * @return list<string>
     */
    public function sheetNames(string $path, string $extension): array
    {
        if ($this->isCsv($extension)) {
            return ['Sheet1'];
        }

        return array_keys($this->xlsxSheetTargets($this->openZip($path)));
    }

    /**
     * Rows of the chosen sheet (default: the first sheet that has data). Each
     * row is a list of trimmed strings, padded to the same width.
     *
     * @return list<list<string>>
     */
    public function rows(string $path, string $extension, ?string $sheet = null): array
    {
        $rows = $this->isCsv($extension)
            ? $this->csvRows($path)
            : $this->xlsxRows($path, $sheet);

        $width = 0;
        foreach ($rows as $row) {
            $width = max($width, count($row));
        }

        return array_map(fn (array $row) => array_pad($row, $width, ''), $rows);
    }

    private function isCsv(string $extension): bool
    {
        return in_array(strtolower($extension), ['csv', 'txt'], true);
    }

    /**
     * @return list<list<string>>
     */
    private function csvRows(string $path): array
    {
        $content = (string) file_get_contents($path);
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content) ?? $content;
        if (! mb_check_encoding($content, 'UTF-8')) {
            $content = mb_convert_encoding($content, 'UTF-8', 'Windows-1252');
        }

        $firstLine = strtok($content, "\r\n") ?: '';
        $delimiter = collect([',', ';', "\t"])
            ->sortByDesc(fn (string $d) => substr_count($firstLine, $d))
            ->first();

        $handle = fopen('php://temp', 'r+');
        fwrite($handle, $content);
        rewind($handle);

        $rows = [];
        while (($cells = fgetcsv($handle, null, $delimiter, '"', '')) !== false) {
            if (count($rows) >= self::MAX_ROWS) {
                break;
            }
            $rows[] = array_map(fn ($cell) => trim((string) $cell), $cells);
        }
        fclose($handle);

        return $rows;
    }

    /**
     * @return list<list<string>>
     */
    private function xlsxRows(string $path, ?string $sheet): array
    {
        $zip = $this->openZip($path);
        $targets = $this->xlsxSheetTargets($zip);
        if ($targets === []) {
            throw new RuntimeException('The workbook has no sheets.');
        }

        $strings = $this->sharedStrings($zip);

        $candidates = $sheet !== null && isset($targets[$sheet]) ? [$sheet => $targets[$sheet]] : $targets;
        foreach ($candidates as $target) {
            $rows = $this->sheetRows($zip, $target, $strings);
            if ($rows !== []) {
                $zip->close();

                return $rows;
            }
        }

        $zip->close();

        return [];
    }

    private function openZip(string $path): ZipArchive
    {
        $zip = new ZipArchive;
        if ($zip->open($path, ZipArchive::RDONLY) !== true || $zip->locateName('xl/workbook.xml') === false) {
            throw new RuntimeException('This is not a valid .xlsx file. In Excel, use File → Save As → Excel Workbook (.xlsx) or CSV.');
        }

        return $zip;
    }

    private function readPart(ZipArchive $zip, string $name): ?SimpleXMLElement
    {
        $stat = $zip->statName($name);
        if ($stat === false) {
            return null;
        }
        if ($stat['size'] > self::MAX_PART_BYTES) {
            throw new RuntimeException('The sheet is too large to import. Split it into smaller files.');
        }

        $xml = $zip->getFromName($name);
        if ($xml === false) {
            return null;
        }

        $element = simplexml_load_string($xml, SimpleXMLElement::class, LIBXML_NONET | LIBXML_COMPACT);

        return $element === false ? null : $element;
    }

    /**
     * Sheet name => path of its XML part inside the zip.
     *
     * @return array<string, string>
     */
    private function xlsxSheetTargets(ZipArchive $zip): array
    {
        $workbook = $this->readPart($zip, 'xl/workbook.xml');
        $rels = $this->readPart($zip, 'xl/_rels/workbook.xml.rels');
        if ($workbook === null || $rels === null) {
            return [];
        }

        $targetById = [];
        foreach ($rels->children() as $rel) {
            $attributes = $rel->attributes();
            $target = (string) $attributes['Target'];
            $target = str_starts_with($target, '/') ? ltrim($target, '/') : 'xl/'.$target;
            $targetById[(string) $attributes['Id']] = $target;
        }

        // Elements here are namespaced but their plain attributes are not, so
        // attributes are read with attributes() rather than $node['name'].
        $sheets = [];
        foreach ($workbook->children(self::NS_MAIN)->sheets->sheet ?? [] as $node) {
            $id = (string) $node->attributes(self::NS_REL)['id'];
            if (isset($targetById[$id])) {
                $sheets[(string) $node->attributes()['name']] = $targetById[$id];
            }
        }

        return $sheets;
    }

    /**
     * @return list<string>
     */
    private function sharedStrings(ZipArchive $zip): array
    {
        $xml = $this->readPart($zip, 'xl/sharedStrings.xml');
        if ($xml === null) {
            return [];
        }

        $strings = [];
        foreach ($xml->children(self::NS_MAIN)->si as $si) {
            $text = (string) $si->t;
            foreach ($si->r as $run) {
                $text .= (string) $run->t;
            }
            $strings[] = $text;
        }

        return $strings;
    }

    /**
     * @param  list<string>  $strings
     * @return list<list<string>>
     */
    private function sheetRows(ZipArchive $zip, string $target, array $strings): array
    {
        $xml = $this->readPart($zip, $target);
        if ($xml === null) {
            return [];
        }

        $rows = [];
        foreach ($xml->children(self::NS_MAIN)->sheetData->row ?? [] as $rowNode) {
            $rowNumber = (int) $rowNode->attributes()['r'] ?: count($rows) + 1;
            if ($rowNumber > self::MAX_ROWS) {
                break;
            }

            $cells = [];
            $position = 0;
            foreach ($rowNode->c as $cell) {
                $reference = (string) $cell->attributes()['r'];
                $column = $reference !== '' ? $this->columnIndex($reference) : $position;
                $cells[$column] = $this->cellValue($cell, $strings);
                $position = $column + 1;
            }

            $row = $cells === [] ? [] : array_replace(array_fill(0, max(array_keys($cells)) + 1, ''), $cells);
            $rows[$rowNumber - 1] = array_map('trim', $row);
        }

        if ($rows === []) {
            return [];
        }

        // Keep row numbers aligned with Excel: fill skipped rows with blanks.
        $filled = [];
        for ($i = 0, $last = max(array_keys($rows)); $i <= $last; $i++) {
            $filled[] = $rows[$i] ?? [];
        }

        while ($filled !== [] && implode('', end($filled)) === '') {
            array_pop($filled);
        }

        return $filled;
    }

    /**
     * @param  list<string>  $strings
     */
    private function cellValue(SimpleXMLElement $cell, array $strings): string
    {
        $type = (string) $cell->attributes()['t'];

        return match ($type) {
            's' => $strings[(int) $cell->v] ?? '',
            'inlineStr' => (string) $cell->is->t.implode('', array_map(fn ($r) => (string) $r->t, iterator_to_array($cell->is->r ?? [], false))),
            'b' => ((string) $cell->v) === '1' ? 'TRUE' : 'FALSE',
            default => (string) $cell->v,
        };
    }

    /** "C12" -> 2 (zero-based column). */
    private function columnIndex(string $reference): int
    {
        $letters = strtoupper((string) preg_replace('/\d+/', '', $reference));
        $index = 0;
        foreach (str_split($letters) as $letter) {
            $index = $index * 26 + (ord($letter) - 64);
        }

        return max(0, $index - 1);
    }
}
