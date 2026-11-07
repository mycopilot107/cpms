<?php

namespace Tests\Feature;

use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class TrialPeriodTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array{string, string}>
     */
    public static function signups(): array
    {
        return [
            'early in the month: covers that month-end plus a week' => ['2026-11-01 10:00', '2026-12-07 23:59:59'],
            '30 days and month-end + a week fall on the same day' => ['2026-11-07 09:00', '2026-12-07 23:59:59'],
            'late in the month: the next month-end counts' => ['2026-11-20 12:00', '2027-01-07 23:59:59'],
            'last days of the month' => ['2026-11-29 18:30', '2027-01-07 23:59:59'],
            'February is short' => ['2027-02-10 08:00', '2027-03-12 08:00:00'],
        ];
    }

    #[DataProvider('signups')]
    public function test_trial_lasts_30_days_and_past_the_first_month_end(string $signedUp, string $expectedEnd): void
    {
        $this->assertSame($expectedEnd, Company::trialEndsAt(Carbon::parse($signedUp))->toDateTimeString());
    }

    public function test_trial_is_never_shorter_than_30_days(): void
    {
        foreach (range(0, 365, 7) as $offset) {
            $start = Carbon::parse('2026-01-01 12:00')->addDays($offset);

            $this->assertGreaterThanOrEqual(30, $start->diffInDays(Company::trialEndsAt($start)), $start->toDateString());
        }
    }

    public function test_new_sign_up_gets_the_month_end_trial(): void
    {
        $this->travelTo(Carbon::parse('2026-11-20 12:00'));

        $this->post(route('register.store'), [
            'company_name' => 'Late Month Builders',
            'name' => 'Ravi',
            'email' => 'ravi@latemonth.test',
            'country' => 'India',
            'currency' => 'INR',
            'password' => 'Secret-pass-123',
            'password_confirmation' => 'Secret-pass-123',
        ])->assertRedirect(route('dashboard'));

        $company = Company::query()->where('email', 'ravi@latemonth.test')->sole();
        $this->assertSame('2027-01-07 23:59:59', $company->trial_ends_at->toDateTimeString());
        $this->assertDatabaseHas('subscriptions', ['company_id' => $company->id, 'trial_ends_at' => '2027-01-07 23:59:59']);
    }
}
