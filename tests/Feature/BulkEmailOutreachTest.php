<?php

namespace Tests\Feature;

use App\Models\SendEmail;
use App\Models\User;
use App\Services\PublicBusinessEmailDiscoveryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Mockery\MockInterface;
use Tests\TestCase;

class BulkEmailOutreachTest extends TestCase
{
    use RefreshDatabase;

    private const SUBJECT = 'Try Best CPMS';

    private const BODY = '<p>Hello</p>';

    public function test_page_opens_without_login_outside_production(): void
    {
        $this->get(route('admin.send-email.index'))
            ->assertOk()
            ->assertSee('Send email to contractors');
    }

    public function test_page_is_hidden_in_production_when_no_access_key_is_set(): void
    {
        $this->app->detectEnvironment(fn () => 'production');
        config(['outreach.access_key' => null]);

        $this->get(route('admin.send-email.index'))->assertNotFound();
    }

    public function test_access_key_unlocks_the_page_for_the_session(): void
    {
        config(['outreach.access_key' => 'secret-key']);

        $this->get(route('admin.send-email.index'))->assertNotFound();
        $this->get(route('admin.send-email.index', ['key' => 'wrong']))->assertNotFound();
        $this->get(route('admin.send-email.index', ['key' => 'secret-key']))->assertOk();
        $this->get(route('admin.send-email.index'))->assertOk();
    }

    public function test_search_queues_new_emails_and_skips_known_and_customer_addresses(): void
    {
        SendEmail::create(['email' => 'old@builder.test', 'subject' => 'x', 'body' => 'x']);
        User::factory()->create(['email' => 'customer@builder.test']);
        $this->mock(PublicBusinessEmailDiscoveryService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('discover')->once()->andReturn([
                'new@builder.test', 'OLD@builder.test', 'customer@builder.test',
            ]);
        });

        $this->post(route('admin.send-email.search'), [
            'query' => 'construction contractors indore',
            'subject' => self::SUBJECT,
            'body' => self::BODY,
        ])->assertRedirect(route('admin.send-email.index'));

        $this->assertDatabaseHas('send_email', [
            'email' => 'new@builder.test',
            'search_query' => 'construction contractors indore',
        ]);
        $this->assertSame(1, SendEmail::query()->where('email', 'old@builder.test')->count());
        $this->assertDatabaseMissing('send_email', ['email' => 'customer@builder.test']);
    }

    public function test_pasted_list_is_deduplicated(): void
    {
        $this->post(route('admin.send-email.store'), [
            'emails' => "a@site.test, A@site.test\nb@site.test, not-an-email",
            'subject' => self::SUBJECT,
            'body' => self::BODY,
        ])->assertRedirect(route('admin.send-email.index'));

        $this->assertSame(['a@site.test', 'b@site.test'], SendEmail::query()->orderBy('email')->pluck('email')->all());
    }

    public function test_send_now_emails_each_address_once_with_an_unsubscribe_link(): void
    {
        Mail::fake();
        $row = SendEmail::create(['email' => 'a@site.test', 'subject' => self::SUBJECT, 'body' => self::BODY]);

        $this->post(route('admin.send-email.send-now'))->assertRedirect();
        $this->post(route('admin.send-email.send-now'))->assertRedirect();

        $row->refresh();
        $this->assertTrue($row->is_send);
        $this->assertNotNull($row->sent_at);
        $this->artisan('emails:send-pending')->assertSuccessful();
        $this->assertSame(1, SendEmail::query()->where('is_send', true)->count());
    }

    public function test_unsubscribed_address_is_not_sent(): void
    {
        Mail::fake();
        $row = SendEmail::create(['email' => 'stop@site.test', 'subject' => self::SUBJECT, 'body' => self::BODY]);

        $this->get(URL::signedRoute('email.unsubscribe', ['sendEmail' => $row->id]))
            ->assertOk()
            ->assertSee('You are unsubscribed');

        $this->artisan('emails:send-pending')->assertSuccessful();

        $row->refresh();
        $this->assertNotNull($row->unsubscribed_at);
        $this->assertFalse($row->is_send);
    }

    public function test_unsubscribe_link_without_a_valid_signature_is_rejected(): void
    {
        $row = SendEmail::create(['email' => 'x@site.test', 'subject' => self::SUBJECT, 'body' => self::BODY]);

        $this->get(route('email.unsubscribe', ['sendEmail' => $row->id]))->assertForbidden();

        $this->assertNull($row->refresh()->unsubscribed_at);
    }
}
