<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_login_page_labels_the_android_app_link(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('Get the Android app', false)
            ->assertSee('On Google Play', false)
            ->assertSee('aria-label="Get the Best CPMS Android app on Google Play"', false);
    }
}
