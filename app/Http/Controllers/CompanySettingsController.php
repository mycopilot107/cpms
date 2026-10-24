<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateCompanySettingsRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * The signed-in owner's or admin's own company profile: name, address,
 * country, the currency project costs are shown in, and a tax ID.
 */
class CompanySettingsController extends Controller
{
    public function edit(Request $request): View
    {
        abort_unless($request->user()->isAdmin(), 403);

        return view('company.settings', ['company' => $request->user()->company]);
    }

    public function update(UpdateCompanySettingsRequest $request): RedirectResponse
    {
        $request->user()->company->update($request->validated());

        return redirect()
            ->route('company.settings')
            ->with('status', 'Company settings saved.');
    }
}
