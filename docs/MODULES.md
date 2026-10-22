# Best CPMS — Finance, Planning & Reports modules

This document covers the three modules added on top of the MVP (Projects /
Tasks / Team / Billing). They follow every convention already established in the
app: server-rendered Blade + Tailwind (CDN, no build step), the
`BelongsToCompany` tenant global scope, guarded ownership keys, auto-discovered
policies, and scoped foreign-key validation. **No new Composer packages were
added.**

---

## 1. Finance

Tracks the money side of each project: the bill of quantities (BOQ), expenses,
and payments in/out, with budget-vs-spend roll-ups.

### Screens

| URL | Name | What it shows |
|-----|------|---------------|
| `/finance` | `finance.index` | Per-project budget, BOQ total, expenses, received, paid + company totals |
| `/boq-items` | `boq-items.*` | Bill of quantities (item, unit, qty × rate = amount) |
| `/expenses` | `expenses.*` | Expenses by category, vendor, date |
| `/project-payments` | `project-payments.*` | Incoming/outgoing payments with method + reference |

BOQ, expenses and payments are full resource controllers (no `show`). The
finance landing page links across to all three.

### Data model

- **`boq_items`** — `project_id`, `item_code`, `category`, `description`,
  `unit`, `quantity` (decimal 15,3), `rate` (decimal 15,2), `amount`
  (decimal 18,2), `sort_order`.
- **`expenses`** — `project_id`, `category`
  (material/labour/equipment/transport/subcontractor/overhead/other),
  `description`, `amount`, `expense_date`, `vendor`, `reference_no`.
- **`project_payments`** — `project_id`, `direction` (incoming/outgoing),
  `party_name`, `amount`, `payment_date`, `method`
  (cash/bank_transfer/cheque/upi/card/other), `reference_no`, `notes`. Named
  `project_payments` (not `payments`) to avoid colliding with the
  subscription-billing `payments` table.

All three carry `company_id` + `created_by` (guarded) and cascade on project
delete. `Project` gains `boqItems()`, `expenses()`, `projectPayments()`
relations plus roll-up helpers `boqTotal()`, `expenseTotal()`,
`paymentsReceived()`, `paymentsPaid()`, and `budgetVariance()`.

### Server-authoritative amounts

The BOQ line `amount` is **always recomputed on the server** as
`round(quantity * rate, 2)` in the controller — the value posted from the
browser is never trusted. The create/edit form shows a live JS preview only for
UX; the saved figure comes from the server. (This mirrors how the billing
module computes subscription price server-side.)

---

## 2. Planning

Schedule views over the tasks and milestones you already have — no new
scheduling engine, just new ways to look at existing dates.

| URL | Name | What it shows |
|-----|------|---------------|
| `/planning/calendar` | `planning.calendar` | Month grid (Mon-first) of tasks (indigo) + milestones (amber), with prev/next month nav |
| `/planning/gantt` | `planning.gantt` | Horizontal timeline of tasks positioned by start/due date |
| `/milestones` | `milestones.*` | Milestone CRUD (name, due date, status) |

- **`milestones`** table — `project_id`, `name`, `description`, `due_date`,
  `status` (pending/in_progress/completed/delayed), `completed_at`,
  `sort_order`. `completed_at` is stamped/cleared automatically as the status
  crosses the "completed" line.
- The **Gantt** is CSS-only: each bar is percent-positioned (`left` / `width`)
  across a data-derived date window, coloured by state
  (overdue = rose, complete = emerald, scheduled = indigo), with month tick
  marks and a "today" line. No JS charting library.
- The **calendar** is a 7-column CSS grid; leading blanks are computed from the
  first day's ISO weekday so the month starts on the right column.

---

## 3. Reports

Four read-only reports, each rendered as a responsive table and downloadable as
CSV (`?export=csv`) for Excel / Google Sheets. CSV is streamed with
`response()->streamDownload()` + `fputcsv` — no package needed.

| URL | Name | Contents |
|-----|------|----------|
| `/reports` | `reports.index` | Hub linking the four reports |
| `/reports/projects` | `reports.projects` | Status, schedule, budget, expenses, variance, task count, progress |
| `/reports/tasks` | `reports.tasks` | Every task with assignee/status/due/progress; filter by project + status |
| `/reports/cost` | `reports.cost` | Expenses grouped by category with share bars; budget, payments, variance, net cash flow; filter by project |
| `/reports/progress` | `reports.progress` | Per-project completion % and completed/in-progress/pending/overdue counts |

All aggregation uses tenant-scoped Eloquent (`withSum`, `withCount`,
`withAvg`, and grouped `selectRaw`), so figures never cross company boundaries.

---

## 4. Authorization

Two new role groups on `User` (each also requires `isActive()`):

```
FINANCE_ROLES   = owner, admin, manager, accountant   → canViewFinance() / canManageFinance()
                                                       → canViewReports()
PLANNING_ROLES  = owner, admin, manager, supervisor   → canManagePlanning()
```

- **Finance** pages (BOQ, expenses, payments, the finance dashboard) and **all
  Reports** require a finance role. Enforced by `BoqItemPolicy`,
  `ExpensePolicy`, `ProjectPaymentPolicy` (view = `canViewFinance`, write =
  `canManageFinance`) and by `abort_unless($user->canViewFinance()/…ViewReports(), 403)`
  in the finance/report controllers.
- **Planning** calendar + Gantt are visible to any active member; **milestone
  writes** require a planning role (`MilestonePolicy`).
- Every policy also re-checks `company_id` (a private `sameCompany()` helper),
  so tenant isolation holds even for privileged roles — consistent with the
  rest of the app (no cross-tenant `before()` hook).

Nav links appear conditionally: **Planning** for all active users, **Finance**
and **Reports** only for finance roles.

---

## 5. Running it

The three modules add four migrations (`boq_items`, `expenses`,
`project_payments`, `milestones`). From the project root on a machine with PHP +
MySQL:

```sh
php artisan migrate        # applies the four new tables
# or, for a clean slate with demo data:
php artisan migrate:fresh --seed
php artisan serve
```

No front-end build is required (Tailwind is loaded from its CDN).

---

## 6. Verification

Verified statically (no PHP in the build sandbox): brace balance across all new
PHP files, matched Blade directives, and confirmation that every `route()`,
`view()`, and `@include()` reference resolves. Resource route parameters for
`boq-items` and `project-payments` are pinned (`->parameters([...])`) to the
camelCase names their form requests expect, so update-vs-create authorization
resolves correctly. Migration order satisfies all foreign-key dependencies
(company + project exist before these tables reference them).
