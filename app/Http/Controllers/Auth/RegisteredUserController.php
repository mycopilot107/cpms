<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\CompanyProvisioner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
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
    public function store(RegisterRequest $request, CompanyProvisioner $provisioner): RedirectResponse
    {
        $data = $request->validated();

        $user = $provisioner->provision(
            company: [
                'name' => $data['company_name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'country' => $data['country'],
                'currency' => $data['currency'],
            ],
            owner: [
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'password' => $data['password'],
            ],
        );

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')
            ->with('status', 'Welcome to Best CPMS! Your free trial runs until '.$user->company->trial_ends_at->format('j M Y').' — long enough to close your first month-end.');
    }
}
