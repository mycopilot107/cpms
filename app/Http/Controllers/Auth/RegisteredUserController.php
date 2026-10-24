<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Company;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Register a new tenant: company + owner user + trial subscription,
     * all created atomically so a half-provisioned tenant can never exist.
     */
    public function store(RegisterRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $user = DB::transaction(function () use ($data): User {
            $now = now();
            $trialEndsAt = now()->addDays(Company::TRIAL_DAYS);

            $company = Company::create([
                'name' => $data['company_name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'status' => Company::STATUS_ACTIVE,
                'trial_started_at' => $now,
                'trial_ends_at' => $trialEndsAt,
            ]);

            // company_id / role / status are guarded, so set them explicitly
            // here rather than through mass assignment.
            $user = new User;
            $user->company_id = $company->id;
            $user->name = $data['name'];
            $user->email = $data['email'];
            $user->phone = $data['phone'] ?? null;
            $user->password = $data['password']; // hashed by the model cast
            $user->assignRole(User::ROLE_OWNER);
            $user->status = 'active';
            $user->email_verified_at = $now;
            $user->save();

            Subscription::create([
                'company_id' => $company->id,
                'plan' => Subscription::PLAN_TRIAL,
                'status' => Subscription::STATUS_TRIAL,
                'trial_starts_at' => $now,
                'trial_ends_at' => $trialEndsAt,
            ]);

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')
            ->with('status', 'Welcome to Best CPMS! Your 15-day free trial has started.');
    }
}
