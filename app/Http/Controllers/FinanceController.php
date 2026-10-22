<?php

namespace App\Http\Controllers;

use App\Models\BoqItem;
use App\Models\Expense;
use App\Models\Project;
use App\Models\ProjectPayment;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FinanceController extends Controller
{
    /**
     * Finance overview: every project with its budget, priced BOQ value, actual
     * expenses, payments received / paid and the budget variance. All figures
     * are aggregated in SQL and auto-scoped to the tenant by BelongsToCompany.
     */
    public function index(Request $request): View
    {
        abort_unless($request->user()->canViewFinance(), 403);

        $projects = Project::query()
            ->withSum('boqItems as boq_total', 'amount')
            ->withSum('expenses as expense_total', 'amount')
            ->withSum(['projectPayments as received_total' => function ($query) {
                $query->where('direction', ProjectPayment::DIRECTION_INCOMING);
            }], 'amount')
            ->withSum(['projectPayments as paid_total' => function ($query) {
                $query->where('direction', ProjectPayment::DIRECTION_OUTGOING);
            }], 'amount')
            ->orderBy('name')
            ->paginate(15);

        // Company-wide roll-up for the summary cards (auto-scoped to tenant).
        $totals = [
            'budget' => (float) Project::sum('budget'),
            'boq' => (float) BoqItem::sum('amount'),
            'expenses' => (float) Expense::sum('amount'),
            'received' => (float) ProjectPayment::where('direction', ProjectPayment::DIRECTION_INCOMING)->sum('amount'),
            'paid' => (float) ProjectPayment::where('direction', ProjectPayment::DIRECTION_OUTGOING)->sum('amount'),
        ];

        return view('finance.index', [
            'projects' => $projects,
            'totals' => $totals,
        ]);
    }
}
