<?php

namespace Tests\Feature;

use Tests\TestCase;

class GooglePlayDownloadTest extends TestCase
{
    private const STORE_URL = 'https://play.google.com/store/apps/details?id=com.bestcpms.cpms_mobile';

    private const TESTING_URL = 'https://play.google.com/apps/internaltest/4700000000000000000';

    public function test_page_links_to_the_play_store_listing(): void
    {
        config(['mobile.play_store_url' => self::STORE_URL, 'mobile.play_testing_url' => self::TESTING_URL]);

        $this->get(route('app.download-page'))
            ->assertOk()
            ->assertSee(self::STORE_URL, false)
            ->assertSee('Get it on')
            ->assertDontSee(self::TESTING_URL, false)
            ->assertDontSee('APK')
            ->assertDontSee('Allow the install');
    }

    public function test_page_links_to_internal_testing_and_explains_the_invite(): void
    {
        config(['mobile.play_store_url' => null, 'mobile.play_testing_url' => self::TESTING_URL]);

        $this->get(route('app.download-page'))
            ->assertOk()
            ->assertSee(self::TESTING_URL, false)
            ->assertSee('Join the test on')
            ->assertSee('Become a tester');
    }

    public function test_page_says_coming_soon_without_a_play_link(): void
    {
        config(['mobile.play_store_url' => null, 'mobile.play_testing_url' => null]);

        $this->get(route('app.download-page'))
            ->assertOk()
            ->assertSee('Coming soon to Google Play');
    }

    public function test_old_download_link_redirects_to_google_play(): void
    {
        config(['mobile.play_store_url' => null, 'mobile.play_testing_url' => self::TESTING_URL]);

        $this->get(route('app.download'))->assertRedirect(self::TESTING_URL);
    }

    public function test_old_download_link_never_serves_a_file(): void
    {
        config(['mobile.play_store_url' => null, 'mobile.play_testing_url' => null]);

        $this->get(route('app.download'))
            ->assertRedirect(route('app.download-page'))
            ->assertHeaderMissing('content-disposition');
    }
}
