<?php

namespace Tests\Feature;

use Tests\TestCase;

class AndroidAppDownloadTest extends TestCase
{
    private const STORE_URL = 'https://play.google.com/store/apps/details?id=com.bestcpms.cpms_mobile';

    private const TESTING_URL = 'https://play.google.com/apps/internaltest/4700000000000000000';

    private string $apkPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->apkPath = sys_get_temp_dir().'/cpms-test-'.uniqid().'.apk';
        config([
            'mobile.play_store_url' => null,
            'mobile.play_testing_url' => null,
            'mobile.apk_path' => $this->apkPath,
        ]);
    }

    protected function tearDown(): void
    {
        if (is_file($this->apkPath)) {
            unlink($this->apkPath);
        }

        parent::tearDown();
    }

    private function deployApk(int $bytes = 2 * 1048576): void
    {
        file_put_contents($this->apkPath, str_repeat('a', $bytes));
    }

    public function test_page_links_to_the_play_store_listing(): void
    {
        config(['mobile.play_store_url' => self::STORE_URL, 'mobile.play_testing_url' => self::TESTING_URL]);

        $this->get(route('app.download-page'))
            ->assertOk()
            ->assertSee(self::STORE_URL, false)
            ->assertSee('Get it on')
            ->assertDontSee(self::TESTING_URL, false)
            ->assertDontSee('Download APK');
    }

    public function test_page_links_to_internal_testing_and_explains_the_invite(): void
    {
        config(['mobile.play_testing_url' => self::TESTING_URL]);

        $this->get(route('app.download-page'))
            ->assertOk()
            ->assertSee(self::TESTING_URL, false)
            ->assertSee('Join the test on')
            ->assertSee('Become a tester');
    }

    public function test_page_says_coming_soon_without_any_install_option(): void
    {
        $this->get(route('app.download-page'))
            ->assertOk()
            ->assertSee('Coming soon to Google Play')
            ->assertDontSee('Download APK');
    }

    public function test_page_offers_the_apk_with_its_size_and_install_steps(): void
    {
        $this->deployApk(2 * 1048576);

        $this->get(route('app.download-page'))
            ->assertOk()
            ->assertSee('Download APK')
            ->assertSee('2 MB')
            ->assertSee(route('app.download'), false)
            ->assertSee('Allow from this source')
            ->assertDontSee('Coming soon to Google Play');
    }

    public function test_page_offers_google_play_and_the_apk_together(): void
    {
        config(['mobile.play_testing_url' => self::TESTING_URL]);
        $this->deployApk();

        $this->get(route('app.download-page'))
            ->assertOk()
            ->assertSee(self::TESTING_URL, false)
            ->assertSee('Download APK')
            ->assertSee('Installing from Google Play')
            ->assertSee('Installing the APK');
    }

    public function test_download_link_streams_the_apk(): void
    {
        config(['mobile.play_testing_url' => self::TESTING_URL]);
        $this->deployApk(1024);

        $response = $this->get(route('app.download'));

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/vnd.android.package-archive')
            ->assertDownload('BestCPMS-Field.apk');
        $this->assertSame(1024, (int) $response->headers->get('Content-Length'));
    }

    public function test_download_link_falls_back_to_google_play_without_an_apk(): void
    {
        config(['mobile.play_testing_url' => self::TESTING_URL]);

        $this->get(route('app.download'))->assertRedirect(self::TESTING_URL);
    }

    public function test_download_link_returns_to_the_app_page_when_nothing_is_available(): void
    {
        $this->get(route('app.download'))
            ->assertRedirect(route('app.download-page'))
            ->assertHeaderMissing('content-disposition');
    }
}
