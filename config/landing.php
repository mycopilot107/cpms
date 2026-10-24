<?php

/*
|--------------------------------------------------------------------------
| Keyword landing pages (Solutions)
|--------------------------------------------------------------------------
| Data-driven SEO landing pages that target specific "construction project
| management" search intents (by audience and by use-case). One Blade
| template (resources/views/public/landing.blade.php) renders every entry,
| and routes/web.php registers one named route per key — so adding a page is
| just adding an array entry here.
|
| IMPORTANT — this file must stay cacheable by `php artisan config:cache`:
|   * Pure scalars/arrays only. NO route() calls, NO closures, NO model
|     constants. Deep-link targets are stored as route *names* (strings) and
|     resolved with route() in the template.
|   * Pricing/trial figures are written as tokens (:symbol, :perUser,
|     :minSeats, :fromMonth, :trialDays) and substituted in the template from
|     config/billing.php, so there is a single source of truth for pricing.
|
| SEO guardrails (consistent with the rest of the site): every feature maps
| to a capability the product genuinely has, each page has distinct copy and
| its own focused keyword (no thin/doorway near-duplicates), and we emit only
| honest structured data (Service + BreadcrumbList + FAQPage) — never fake
| ratings or reviews.
|
| Each entry:
|   slug             URL path (no leading slash), unique, hyphenated.
|   keyword          The primary search phrase this page targets.
|   breadcrumb       Short label for the breadcrumb trail + BreadcrumbList.
|   kicker           Small eyebrow label in the hero.
|   title            <title> / og:title (meta_description/keywords too).
|   meta_description
|   meta_keywords
|   h1               The on-page H1 (may differ from <title>).
|   lead             One-paragraph hero sub-headline.
|   intro            2 paragraphs: "what this is / why it matters" prose.
|   service_type     schema.org Service.serviceType string.
|   features_heading Section heading above the feature grid.
|   features         3-6 items: title, body, and optional deep-link `route`.
|   band             A highlighted "who it's for" / emphasis block.
|   faqs             2-3 Q/A pairs (also emitted as FAQPage structured data).
*/

return [

    'pages' => [

        /* ---------------------------------------------------------------- */
        /* Priority: construction project management software India */
        /* ---------------------------------------------------------------- */
        'india' => [
            'slug' => 'construction-project-management-software-india',
            'keyword' => 'construction project management software India',
            'breadcrumb' => 'Software in India',
            'kicker' => 'Solutions · India',
            'title' => 'Construction Project Management Software India',
            'meta_description' => 'Construction project management software India teams use to plan sites, control BOQ and budgets, track expenses, and report progress. Priced and billed in INR through Razorpay, with a free trial and no card.',
            'meta_keywords' => 'construction project management software India, construction software India, construction management system India, BOQ software India, construction budget software India, Razorpay construction software',
            'h1' => 'Construction project management software India',
            'lead' => 'Plan the programme, hold the budget, and see site progress from one system built for construction companies in India. Prices are in rupees and payment is collected in INR.',
            'intro' => [
                'Indian contractors and builders usually run several sites at once, with the programme in one sheet, the bill of quantities in another, and expenses arriving by message. Best CPMS is construction project management software India teams can run in the browser: each project keeps its schedule, tasks, budget, and site updates together.',
                'The company record can store a GST number, the plan is priced per user in INR, and checkout charges Indian rupees through Razorpay. A new company starts on a :trialDays-day free trial, then licenses the seats it needs — minimum :minSeats users, from :symbol:fromMonth per month.',
            ],
            'service_type' => 'Construction project management software in India',
            'features_heading' => 'What Indian construction teams run in Best CPMS',
            'features' => [
                ['title' => 'INR pricing and Razorpay checkout', 'body' => 'The paid plan is quoted and charged in Indian rupees. Owners and admins pick seats and a 1, 3, 6, or 12 month term, and the total is calculated on the server.', 'route' => 'pricing'],
                ['title' => 'GST on the company profile', 'body' => 'Each company can store its GST number with its address, so commercial records sit with the same account that runs the projects.', 'route' => null],
                ['title' => 'BOQ, budget, and expenses', 'body' => 'Build a bill of quantities, set a project budget, and record expenses and payments against that budget, with totals recomputed on the server.', 'route' => 'landing.budget'],
                ['title' => 'Site progress on a phone', 'body' => 'Supervisors and workers update task status, percent complete, and hours from the field, so the office is not waiting on a weekly sheet.', 'route' => 'services.field'],
                ['title' => 'Reports you can export', 'body' => 'Project, task, cost, and progress reports export to CSV for client updates, billing, and internal reviews.', 'route' => 'landing.reporting'],
                ['title' => 'Office and site on one account', 'body' => 'The interface is in English. Managers and accountants keep the cost screens, while supervisors and workers update the tasks they are assigned.', 'route' => 'features'],
            ],
            'band' => [
                'heading' => 'Priced for a contracting team in India',
                'body' => 'Start on a free trial, then pay :symbol:perUser per user per month in INR. There is no per-project fee and no separate module to unlock.',
            ],
            'faqs' => [
                ['q' => 'Is Best CPMS construction project management software for India?', 'a' => 'Yes. It is built for contractors, builders, and developers working in India. Plans are priced in INR, checkout charges Indian rupees through Razorpay, and each company can store a GST number.'],
                ['q' => 'Can I pay in rupees?', 'a' => 'Yes. The charge currency is INR. Display can also show a US dollar reference, but the amount collected at checkout is the rupee total computed on the server from seats, months, and the per-user price of :symbol:perUser.'],
                ['q' => 'Do I need a server in my office?', 'a' => 'No. Best CPMS runs in the browser, and site staff can update tasks from a phone. A new company gets a :trialDays-day free trial with no card, then a paid plan from :symbol:fromMonth per month for :minSeats users.'],
            ],
        ],

        /* ---------------------------------------------------------------- */
        /* Priority: construction budget management software */
        /* ---------------------------------------------------------------- */
        'budget' => [
            'slug' => 'construction-budget-management-software',
            'keyword' => 'construction budget management software',
            'breadcrumb' => 'Budget Management',
            'kicker' => 'Solutions · Budget',
            'title' => 'Construction Budget Management Software',
            'meta_description' => 'Construction budget management software that ties the project budget to the bill of quantities, expenses, and payments. See what is committed against what was planned, with totals recomputed on the server. Free trial.',
            'meta_keywords' => 'construction budget management software, construction budget tracking software, project budget software for construction, BOQ budget software, construction cost budget India',
            'h1' => 'Construction budget management software',
            'lead' => 'Set the project budget once, then measure the bill of quantities, expenses, and payments against it. Best CPMS keeps the planned number and the spent number on the same job.',
            'intro' => [
                'A construction budget fails when the estimate, the purchase record, and the site spend never meet. Construction budget management software in Best CPMS starts from the budget on the project and the bill of quantities behind it, then lines up expenses and incoming and outgoing payments so the remaining amount is visible while the job is still running.',
                'Finance roles — owner, admin, manager, and accountant — see the cost picture. Totals are calculated on the server from the quantities, rates, and entries you save, so a browser cannot invent a budget figure. Cost reports export to CSV when a client or a lender asks where the money went.',
            ],
            'service_type' => 'Construction budget management software',
            'features_heading' => 'Hold the budget from estimate to spend',
            'features' => [
                ['title' => 'A budget on every project', 'body' => 'Each project stores its own budget alongside the client, site, and dates, so the commercial target sits with the job it belongs to.', 'route' => null],
                ['title' => 'BOQ as the cost backbone', 'body' => 'Quantities and rates in the bill of quantities give the budget a structure you can check, instead of a single lump sum nobody can explain.', 'route' => 'services.cost'],
                ['title' => 'Spend against the plan', 'body' => 'Expenses and project payments post against the same project, so budget versus actual is a running comparison, not a month-end surprise.', 'route' => 'landing.expenses'],
                ['title' => 'Cost reports', 'body' => 'The cost report summarises budget, BOQ, expenses, and payments, and exports to CSV for reviews.', 'route' => 'landing.reporting'],
                ['title' => 'Roles that protect the numbers', 'body' => 'Accountants and managers work the budget. Site workers update tasks. Cost screens stay with the finance roles.', 'route' => 'features'],
            ],
            'band' => [
                'heading' => 'One budget, one job, one set of totals',
                'body' => 'When the BOQ, the expenses, and the payments all point at the project budget, the remaining amount is something you can act on this week.',
            ],
            'faqs' => [
                ['q' => 'How does construction budget management work in Best CPMS?', 'a' => 'You set a budget on the project, build a bill of quantities with quantities and rates, then record expenses and payments on that project. Cost reports compare those figures, and every total is recomputed on the server.'],
                ['q' => 'Who can see the budget?', 'a' => 'Owners, admins, managers, and accountants can open finance and cost reports. Other roles keep working on tasks and milestones without the commercial figures.'],
                ['q' => 'Is budget management a separate product?', 'a' => 'No. It is included in the same per-user plan: :symbol:perUser per user per month, minimum :minSeats users, with a :trialDays-day free trial.'],
            ],
        ],

        /* ---------------------------------------------------------------- */
        /* Priority: construction expense tracking software */
        /* ---------------------------------------------------------------- */
        'expenses' => [
            'slug' => 'construction-expense-tracking-software',
            'keyword' => 'construction expense tracking software',
            'breadcrumb' => 'Expense Tracking',
            'kicker' => 'Solutions · Expenses',
            'title' => 'Construction Expense Tracking Software',
            'meta_description' => 'Construction expense tracking software for recording site and project spend by category, against the job it belongs to. See expenses next to the budget and export the cost report. Free trial, no card.',
            'meta_keywords' => 'construction expense tracking software, construction expense management, site expense tracking software, project expense tracker construction, construction cost tracking India',
            'h1' => 'Construction expense tracking software',
            'lead' => 'Record each construction expense on the project that spent it, then read the total next to the budget. Best CPMS keeps site spend out of notebooks and chat threads.',
            'intro' => [
                'Expense tracking breaks when fuel, materials, labour, and petty cash are written down on different sites and added up later. Construction expense tracking software in Best CPMS attaches every expense to a project, with a category, an amount, and the date, so the job carries its own spend record.',
                'Those expenses sit beside the project budget, the bill of quantities, and client or vendor payments. The cost report can be exported to CSV. Amounts are stored as you enter them and rolled up on the server, which is the figure finance roles see — not a total typed into a browser.',
            ],
            'service_type' => 'Construction expense tracking software',
            'features_heading' => 'Capture spend while the job is open',
            'features' => [
                ['title' => 'Expenses on the right project', 'body' => 'Each expense belongs to one project, so a multi-site contractor can see which job consumed the money.', 'route' => null],
                ['title' => 'Categories you can filter', 'body' => 'Record the kind of spend — material, labour, equipment, transport, subcontractor, overhead, or other — and review it in the finance list.', 'route' => 'services.cost'],
                ['title' => 'Next to the budget', 'body' => 'Expenses are not a side spreadsheet. They count toward the same project budget the estimate was built on.', 'route' => 'landing.budget'],
                ['title' => 'Payments in and out', 'body' => 'Project payments sit alongside expenses, so money received from a client and money spent on site are both on the job.', 'route' => 'landing.payments'],
                ['title' => 'Export the cost report', 'body' => 'Filter the cost report and download CSV when accounts, a client, or an auditor wants the expense list.', 'route' => 'landing.reporting'],
            ],
            'band' => [
                'heading' => 'Stop reconstructing expenses at month end',
                'body' => 'If the expense is entered on the project when it happens, the cost report is already there when someone asks.',
            ],
            'faqs' => [
                ['q' => 'What can I track as a construction expense?', 'a' => 'Any project spend you record in finance: the amount, the date, the category, and the project it belongs to. The list and the cost report then show that spend against the job.'],
                ['q' => 'Can site teams see every expense?', 'a' => 'No. Finance and reports are limited to owners, admins, managers, and accountants. Site roles update tasks and progress without opening the expense ledger.'],
                ['q' => 'Does expense tracking cost extra?', 'a' => 'No. Expense tracking is part of the single plan at :symbol:perUser per user per month, from :minSeats users, after a :trialDays-day free trial.'],
            ],
        ],

        /* ---------------------------------------------------------------- */
        /* Use-case: bill of quantities */
        /* ---------------------------------------------------------------- */
        'boq' => [
            'slug' => 'construction-boq-software',
            'keyword' => 'construction BOQ software',
            'breadcrumb' => 'BOQ Software',
            'kicker' => 'Solutions · Bill of quantities',
            'title' => 'Construction BOQ Software',
            'meta_description' => 'Construction BOQ software for a bill of quantities with item codes, units, quantities, and rates. Line amounts are quantity times rate, recomputed on the server, and tied to the project budget. Free trial.',
            'meta_keywords' => 'construction BOQ software, bill of quantities software, BOQ software India, construction estimating software, quantity and rate BOQ',
            'h1' => 'Construction BOQ software',
            'lead' => 'Build a bill of quantities on the project it belongs to. Each line has a code, a unit, a quantity, and a rate, and the amount is quantity times rate.',
            'intro' => [
                'A BOQ that lives in a spreadsheet drifts the moment someone overwrites a formula. Construction BOQ software in Best CPMS stores each line on the project: item code, category, description, unit, quantity, and rate. The line amount is calculated on the server from quantity and rate, so the browser cannot submit a different total.',
                'Those lines sit with the project budget, the expenses, and the payments. Finance roles use them when they check cost. The same plan that includes scheduling and site tasks includes the BOQ — :symbol:perUser per user per month after a :trialDays-day trial.',
            ],
            'service_type' => 'Construction bill of quantities software',
            'features_heading' => 'A bill of quantities the budget can use',
            'features' => [
                ['title' => 'Lines with units and rates', 'body' => 'Each BOQ item stores a unit, a quantity, and a rate. The amount is quantity times rate, saved by the server.', 'route' => 'services.cost'],
                ['title' => 'Codes and categories', 'body' => 'Item codes and categories keep a long bill readable when you are looking for one trade or one package.', 'route' => null],
                ['title' => 'Tied to the project budget', 'body' => 'The bill belongs to one project, next to that project’s budget, expenses, and payments.', 'route' => 'landing.budget'],
                ['title' => 'Cost report export', 'body' => 'BOQ totals are part of the cost report, which exports to CSV for a client or an auditor.', 'route' => 'landing.reporting'],
            ],
            'band' => [
                'heading' => 'The estimate stays attached to the job',
                'body' => 'When the bill of quantities is a project record, the commercial team and the site are looking at the same lines.',
            ],
            'faqs' => [
                ['q' => 'How is a BOQ line amount calculated?', 'a' => 'The server multiplies the quantity by the rate and stores that amount. A figure typed in the browser is not used as the line total.'],
                ['q' => 'Can I keep a BOQ per project?', 'a' => 'Yes. Every bill item belongs to one project, along with that project’s budget, expenses, and payments.'],
                ['q' => 'Is BOQ software priced separately?', 'a' => 'No. It is included at :symbol:perUser per user per month, minimum :minSeats users, after a :trialDays-day free trial.'],
            ],
        ],

        /* ---------------------------------------------------------------- */
        /* Use-case: Gantt */
        /* ---------------------------------------------------------------- */
        'gantt' => [
            'slug' => 'construction-gantt-chart-software',
            'keyword' => 'construction Gantt chart software',
            'breadcrumb' => 'Gantt Chart',
            'kicker' => 'Solutions · Gantt',
            'title' => 'Construction Gantt Chart Software',
            'meta_description' => 'Construction Gantt chart software that plots tasks with start and due dates on a month timeline, filterable by project, with a today marker. Pair it with the calendar and milestones. Free trial.',
            'meta_keywords' => 'construction Gantt chart software, construction schedule Gantt, project Gantt chart for contractors, construction timeline software India',
            'h1' => 'Construction Gantt chart software',
            'lead' => 'See dated tasks as bars across the months of the job. Filter the chart to one project, and keep a today line on the timeline.',
            'intro' => [
                'A construction programme is hard to read as a list once the dates overlap. The Gantt in Best CPMS draws a bar for every task that has a start date or a due date, stretched across the months those dates cover. You can show every project or one project.',
                'A task can also record a single predecessor, so the sequence is stored with the task even though the chart itself draws the bars rather than dependency arrows. Milestones and the month calendar sit next to the same dates, which is the scheduling set planning roles use.',
            ],
            'service_type' => 'Construction Gantt chart software',
            'features_heading' => 'A timeline made from the tasks you already have',
            'features' => [
                ['title' => 'Bars from real dates', 'body' => 'A task appears on the chart when it has a start date or a due date. Undated tasks stay off the timeline.', 'route' => 'services.planning'],
                ['title' => 'One project or all of them', 'body' => 'Filter the Gantt to a single project when you are in a review, or leave it open to see the wider programme.', 'route' => null],
                ['title' => 'Today on the chart', 'body' => 'When today falls inside the date window, the chart marks it so slippage is visible against the current day.', 'route' => null],
                ['title' => 'Calendar and milestones', 'body' => 'The same dates are available on a month calendar and as milestones with their own status.', 'route' => 'landing.milestones'],
                ['title' => 'One predecessor per task', 'body' => 'Each task can name another task it depends on. A task cannot depend on itself.', 'route' => 'landing.task-management'],
            ],
            'band' => [
                'heading' => 'The chart follows the task dates',
                'body' => 'Change a start or due date on the task and the bar moves. The Gantt is a view of the programme, not a second copy of it.',
            ],
            'faqs' => [
                ['q' => 'What does the Gantt chart show?', 'a' => 'It shows tasks that have a start date or a due date as bars on a month timeline. You can filter to one project. A today marker appears when the current day is inside that window.'],
                ['q' => 'Does the chart draw dependency arrows?', 'a' => 'No. A task can store one predecessor, and that link is kept on the task. The chart draws the dated bars; it does not draw arrows or calculate a critical path.'],
                ['q' => 'Who can open the Gantt?', 'a' => 'Active users can view the calendar and Gantt. Creating and editing milestones is limited to planning roles: owner, admin, manager, and supervisor.'],
            ],
        ],

        /* ---------------------------------------------------------------- */
        /* Use-case: milestones */
        /* ---------------------------------------------------------------- */
        'milestones' => [
            'slug' => 'construction-milestone-tracking-software',
            'keyword' => 'construction milestone tracking software',
            'breadcrumb' => 'Milestones',
            'kicker' => 'Solutions · Milestones',
            'title' => 'Construction Milestone Tracking Software',
            'meta_description' => 'Construction milestone tracking software for due dates and status — pending, in progress, completed, or delayed — on each project. See them beside the calendar and Gantt. Free trial.',
            'meta_keywords' => 'construction milestone tracking software, construction milestone schedule, project milestone tracker, construction programme milestones India',
            'h1' => 'Construction milestone tracking software',
            'lead' => 'Mark the dates the job has to hit, and record whether each one is pending, in progress, completed, or delayed.',
            'intro' => [
                'Tasks describe the work. Milestones describe the points the programme is judged on: foundation complete, structure topped out, handover. Best CPMS keeps each milestone on a project with a name, a description, a due date, and a status.',
                'When a milestone is completed, the completion time is stored. Delayed is its own status, so a slipped date is explicit. Planning roles maintain milestones; the calendar and Gantt show the wider set of dates around them.',
            ],
            'service_type' => 'Construction milestone tracking software',
            'features_heading' => 'Dates you can call done or delayed',
            'features' => [
                ['title' => 'Status you can filter', 'body' => 'Each milestone is pending, in progress, completed, or delayed, so a review is not a guess from a colour on a chart.', 'route' => null],
                ['title' => 'A due date and a completion time', 'body' => 'The due date is the plan. Completed milestones also store when they were marked complete.', 'route' => 'services.planning'],
                ['title' => 'On the project they belong to', 'body' => 'Milestones are not a company-wide list with no owner. Each one is attached to a project.', 'route' => 'landing.project-tracking'],
                ['title' => 'Next to the Gantt', 'body' => 'Open the calendar or the Gantt from the same planning area when you need the task bars around those dates.', 'route' => 'landing.gantt'],
            ],
            'band' => [
                'heading' => 'A delayed milestone stays visible',
                'body' => 'Marking a milestone delayed is a status, not a note that gets lost in a meeting minute.',
            ],
            'faqs' => [
                ['q' => 'What milestone statuses are there?', 'a' => 'Pending, in progress, completed, and delayed. A completed milestone stores the time it was marked complete.'],
                ['q' => 'Who can edit milestones?', 'a' => 'Owners, admins, managers, and supervisors. Other active users can still open the calendar and Gantt.'],
                ['q' => 'Is milestone tracking included?', 'a' => 'Yes. It is part of the same plan at :symbol:perUser per user per month, minimum :minSeats users, with a :trialDays-day free trial.'],
            ],
        ],

        /* ---------------------------------------------------------------- */
        /* Use-case: project payments (client / vendor, not the subscription) */
        /* ---------------------------------------------------------------- */
        'payments' => [
            'slug' => 'construction-payment-tracking-software',
            'keyword' => 'construction payment tracking software',
            'breadcrumb' => 'Payment Tracking',
            'kicker' => 'Solutions · Project payments',
            'title' => 'Construction Payment Tracking Software',
            'meta_description' => 'Construction payment tracking software for money in and money out on each project — cash, bank transfer, cheque, UPI, card, or other. Separate from your Best CPMS subscription. Free trial.',
            'meta_keywords' => 'construction payment tracking software, construction receivables software, project payment tracker, client payment tracking construction India, UPI construction payments',
            'h1' => 'Construction payment tracking software',
            'lead' => 'Record money received from a client and money paid out on the project. This is the job’s cash record, not the subscription you pay for Best CPMS.',
            'intro' => [
                'Construction payment tracking software in Best CPMS stores each project payment as incoming or outgoing, with a method: cash, bank transfer, cheque, UPI, card, or other. The payment belongs to one project, beside that project’s budget and expenses.',
                'Owners, admins, managers, and accountants can open finance. Site roles do not. The cost report can include these payments and export to CSV. Razorpay on this site is only for buying the Best CPMS plan. It does not collect your client’s stage payments.',
            ],
            'service_type' => 'Construction payment tracking software',
            'features_heading' => 'Incoming and outgoing, on the right job',
            'features' => [
                ['title' => 'In or out', 'body' => 'Each payment is incoming or outgoing, so receipts and payouts are not mixed in one unsigned number.', 'route' => null],
                ['title' => 'Methods used on Indian sites', 'body' => 'Record cash, bank transfer, cheque, UPI, card, or other, which matches how site and office payments actually move.', 'route' => 'services.cost'],
                ['title' => 'Against the budget', 'body' => 'Payments sit on the project with the budget, the BOQ, and the expenses.', 'route' => 'landing.budget'],
                ['title' => 'Not the software subscription', 'body' => 'Razorpay checkout is only for the Best CPMS plan. Client and vendor payments are records you enter yourself.', 'route' => 'pricing'],
                ['title' => 'Export with the cost report', 'body' => 'Pull the cost report and download CSV when accounts needs the payment list.', 'route' => 'landing.reporting'],
            ],
            'band' => [
                'heading' => 'Know what the job has been paid',
                'body' => 'A stage payment that is written on the project is visible the next time finance opens that job.',
            ],
            'faqs' => [
                ['q' => 'Does Best CPMS collect my client’s payments?', 'a' => 'No. You record incoming and outgoing project payments yourself. Razorpay is used only when your company pays for the Best CPMS subscription.'],
                ['q' => 'Which payment methods can I record?', 'a' => 'Cash, bank transfer, cheque, UPI, card, and other. Each payment is marked incoming or outgoing and belongs to one project.'],
                ['q' => 'Who can see project payments?', 'a' => 'Owners, admins, managers, and accountants. The record is included in the same per-user plan, from :symbol:fromMonth per month for :minSeats users.'],
            ],
        ],

        /* ---------------------------------------------------------------- */
        /* Audience: main / general contractors */
        /* ---------------------------------------------------------------- */
        'contractors' => [
            'slug' => 'construction-project-management-software-for-contractors',
            'keyword' => 'construction project management software for contractors',
            'breadcrumb' => 'For Contractors',
            'kicker' => 'Solutions · For contractors',
            'title' => 'Construction Project Management Software for Contractors',
            'meta_description' => 'Construction project management software built for contractors — run multiple sites, crews and subcontractors from one programme. Manage BOQ and costs, assign site tasks, and track progress from tender to handover. Free trial, no card.',
            'meta_keywords' => 'construction project management software for contractors, contractor management software, construction software for contractors India, subcontractor management software, BOQ software for contractors, site tracking software',
            'h1' => 'Construction project management software for contractors',
            'lead' => 'Run every site, crew and subcontractor from one programme — not a pile of spreadsheets and WhatsApp groups. Plan the work, control the cost, and see real progress from the field.',
            'intro' => [
                'Contractors rarely run one job at a time. You are juggling several sites at once, each with its own schedule, bill of quantities, crews and subcontractors — and the moment those live in separate spreadsheets and chat groups, something slips. Best CPMS pulls the whole operation into one place so the programme, the budget and the site are always telling the same story.',
                'Set up each project once, break it into milestones and tasks, assign them to the right people, and let the site report progress back to you. Your BOQ, expenses and payments are tracked against budget with every total recomputed on the server, so the numbers you quote and bill from are consistent across every job you run.',
            ],
            'service_type' => 'Construction project management software for contractors',
            'features_heading' => 'What contractors use Best CPMS for',
            'features' => [
                ['title' => 'Many sites, one programme', 'body' => 'Keep every active job in a single workspace with its own schedule, tasks and budget — so you can switch between sites without switching tools.', 'route' => 'services.planning'],
                ['title' => 'BOQ &amp; cost control', 'body' => 'Build your bill of quantities with quantities and rates, track expenses by category, and record payments in and out against each project budget.', 'route' => 'services.cost'],
                ['title' => 'Subcontractors &amp; crews', 'body' => 'Invite your team and subcontractors with role-based access, so each person sees only the tasks and information relevant to their scope.', 'route' => 'features'],
                ['title' => 'Progress from site', 'body' => 'Field staff update task status, percent complete and actual hours from a phone, so the plan on paper matches what is really happening on the ground.', 'route' => 'services.field'],
                ['title' => 'Reports you can hand over', 'body' => 'Pull project, task, cost and progress reports across every job and export to CSV for billing, audits and client updates.', 'route' => 'features'],
                ['title' => 'Server-computed totals', 'body' => 'BOQ values, budgets and payment totals are calculated on the server from your own quantities and rates — never trusted from the browser.', 'route' => null],
            ],
            'band' => [
                'heading' => 'Built for the way contractors actually work',
                'body' => 'Whether you run two sites or twenty, Best CPMS keeps estimation, scheduling, cost control and site reporting connected — so a change on one job never leaves the rest out of date.',
            ],
            'faqs' => [
                ['q' => 'Can I manage more than one construction site at once?', 'a' => 'Yes. Best CPMS is designed for contractors running several jobs in parallel. Each project keeps its own schedule, tasks, BOQ and budget, and you move between them from one workspace — there is no limit tied to the number of active sites.'],
                ['q' => 'Can subcontractors and site crews have limited access?', 'a' => 'Yes. Access is role-based — owner, admin, manager, supervisor, accountant and site worker — so subcontractors and crews see only the tasks and details that concern them, while costs and commercial data stay with the roles you choose.'],
                ['q' => 'How much does it cost for a contracting team?', 'a' => 'Pricing is simple and per-user: :symbol:perUser per user, per month, with a minimum of :minSeats users, so plans start from :symbol:fromMonth per month. Every new company also gets a :trialDays-day free trial with no card required.'],
            ],
        ],

        /* ---------------------------------------------------------------- */
        /* Audience: builders (residential / commercial) */
        /* ---------------------------------------------------------------- */
        'builders' => [
            'slug' => 'construction-management-software-for-builders',
            'keyword' => 'construction management software for builders',
            'breadcrumb' => 'For Builders',
            'kicker' => 'Solutions · For builders',
            'title' => 'Construction Management Software for Builders',
            'meta_description' => 'Construction management software for builders — take a job from estimate to handover without losing control of the schedule or the budget. Plan, cost, track progress and keep clients updated from one system. Free trial.',
            'meta_keywords' => 'construction management software for builders, builder software, home builder project management software, construction estimating and scheduling software, builder cost control software India',
            'h1' => 'Construction management software for builders',
            'lead' => 'Take every job from first estimate to final handover without losing the thread. Keep the schedule, the budget and the client picture in one place that your office and your site both trust.',
            'intro' => [
                'Building work goes sideways in the gaps — between the estimate and the programme, between the budget and what actually gets spent, between what the site knows and what the client is told. Best CPMS closes those gaps by keeping estimation, scheduling, cost and progress in one connected system built for builders.',
                'Start from a bill of quantities, turn it into a programme of milestones and tasks, and track spend against budget as the job runs. When a client asks where things stand, the answer is a current report — not an afternoon spent reconciling spreadsheets.',
            ],
            'service_type' => 'Construction management software for builders',
            'features_heading' => 'From estimate to handover',
            'features' => [
                ['title' => 'Estimate with a BOQ', 'body' => 'Capture quantities and rates in a structured bill of quantities so your estimate becomes the backbone of the budget, not a one-off number.', 'route' => 'services.cost'],
                ['title' => 'Programme the build', 'body' => 'Break the job into milestones and tasks with dependencies, and read the whole programme on a calendar and Gantt timeline.', 'route' => 'services.planning'],
                ['title' => 'Keep jobs on budget', 'body' => 'Track expenses by category and payments in and out against the budget, with totals recomputed on the server so the figures always add up.', 'route' => 'services.cost'],
                ['title' => 'Client-ready progress', 'body' => 'Turn live site status into clean project, cost and progress reports you can export and share at the next client meeting.', 'route' => 'features'],
                ['title' => 'Works on site', 'body' => 'The web app is fully responsive and a companion field app lets site staff report status, progress and hours from a phone.', 'route' => 'app.download-page'],
            ],
            'band' => [
                'heading' => 'One system for the whole job',
                'body' => 'Estimation, scheduling, cost control and progress reporting live together, so builders spend less time reconciling and more time building.',
            ],
            'faqs' => [
                ['q' => 'Can I start from my estimate and reuse it as the budget?', 'a' => 'Yes. You build a bill of quantities with quantities and rates, and those values feed straight into cost tracking — so your estimate becomes the budget you measure spend against, rather than a figure you set aside once the job starts.'],
                ['q' => 'Can I show clients progress without extra paperwork?', 'a' => 'Yes. Because the site updates task status and progress as work happens, project and progress reports are always current. You can export them to CSV and share them, instead of assembling an update by hand each time.'],
                ['q' => 'Is there a free trial for builders trying it out?', 'a' => 'Every new company starts with a :trialDays-day free trial, no credit card required. After that it is :symbol:perUser per user per month, minimum :minSeats users, so plans start from :symbol:fromMonth per month.'],
            ],
        ],

        /* ---------------------------------------------------------------- */
        /* Segment: small construction businesses */
        /* ---------------------------------------------------------------- */
        'small-business' => [
            'slug' => 'construction-management-software-for-small-business',
            'keyword' => 'construction management software for small business',
            'breadcrumb' => 'For Small Business',
            'kicker' => 'Solutions · For small business',
            'title' => 'Construction Management Software for Small Business',
            'meta_description' => 'Affordable construction management software for small businesses — replace scattered spreadsheets with one simple system for schedules, costs and site progress. Transparent per-user pricing and a free trial, with nothing to install.',
            'meta_keywords' => 'construction management software for small business, affordable construction software, small contractor software, construction software for small builders India, simple construction project management',
            'h1' => 'Construction management software for small businesses',
            'lead' => 'You do not need an enterprise rollout to get organised. Best CPMS gives a small construction business one simple, affordable system for schedules, costs and site progress — with nothing to install and no IT team required.',
            'intro' => [
                'For a small builder or contractor, the tool that holds everything together is usually a mix of spreadsheets, printouts and chat messages. It works until a job gets busy — then quantities, costs and who-is-doing-what start to drift. Best CPMS replaces that patchwork with one system you can set up in an afternoon and run from any device.',
                'Pricing is transparent and per-user, so a small team pays for a small team and adds seats only as it grows. There is no build step, no server to maintain and no long contract — you start on a free trial, invite your people, and get to work.',
            ],
            'service_type' => 'Construction management software for small businesses',
            'features_heading' => 'Everything a small team needs, nothing it does not',
            'features' => [
                ['title' => 'Replace the spreadsheets', 'body' => 'Schedule, BOQ, costs and site progress live in one connected workspace instead of a dozen files that fall out of sync.', 'route' => 'cpm-software'],
                ['title' => 'Up and running fast', 'body' => 'No installation and no build step. Invite your team, set up your first project the same day, and work from a laptop or a phone.', 'route' => null],
                ['title' => 'Transparent pricing', 'body' => 'Per-user, per-month pricing with a clear minimum — you always know the total, and you add seats only when you grow.', 'route' => 'pricing'],
                ['title' => 'Costs that stay honest', 'body' => 'BOQ and budget totals are computed on the server from your quantities and rates, so a small team gets numbers it can rely on.', 'route' => 'services.cost'],
                ['title' => 'Field app included', 'body' => 'Your people on site report status, progress and hours from their phones — no separate purchase, no extra setup.', 'route' => 'app.download-page'],
            ],
            'band' => [
                'heading' => 'Priced for a growing business',
                'body' => 'Start small, pay only for the seats you use, and add more as you win more work — without migrating to a different product later.',
            ],
            'faqs' => [
                ['q' => 'Is Best CPMS affordable for a small construction business?', 'a' => 'Yes. Pricing is :symbol:perUser per user, per month, with a minimum of :minSeats users — so plans start from :symbol:fromMonth per month and scale with your headcount. You only ever pay for the seats you actually use.'],
                ['q' => 'Do I need an IT team or a server to run it?', 'a' => 'No. Best CPMS runs in the browser with nothing to install and no server to maintain. If you can use a website, you can use Best CPMS — on a laptop in the office or a phone on site.'],
                ['q' => 'Can I try it before committing?', 'a' => 'Yes. Every new company gets a :trialDays-day free trial with no credit card required, with full access so you can set up a real project and decide before you pay.'],
            ],
        ],

        /* ---------------------------------------------------------------- */
        /* Audience: real-estate developers (portfolio) */
        /* ---------------------------------------------------------------- */
        'developers' => [
            'slug' => 'project-management-software-for-real-estate-developers',
            'keyword' => 'project management software for real estate developers',
            'breadcrumb' => 'For Developers',
            'kicker' => 'Solutions · For real-estate developers',
            'title' => 'Project Management Software for Real-Estate Developers',
            'meta_description' => 'Project management software for real-estate developers — see cost, schedule and progress across a whole portfolio of projects, from groundbreaking to handover, in one system. Free trial, no card required.',
            'meta_keywords' => 'project management software for real estate developers, real estate development software, property development project management, construction portfolio management software, developer cost and schedule tracking India',
            'h1' => 'Project management software for real-estate developers',
            'lead' => 'See cost, schedule and progress across your entire portfolio — not one project at a time. Best CPMS gives developers a single view from groundbreaking to handover, so nothing slips between projects.',
            'intro' => [
                'A developer is accountable for the whole portfolio, but the detail lives inside each individual project. When every site reports differently, forming a reliable view across the portfolio means chasing people for spreadsheets that are already out of date. Best CPMS keeps every project in one system, so the portfolio view is just the current data rolled up.',
                'Each project carries its own programme, bill of quantities, budget and progress; together they give you cost, schedule and completion across every job you have in flight. When a project slips or a cost moves, you see it where it happens — early enough to act.',
            ],
            'service_type' => 'Project management software for real-estate developers',
            'features_heading' => 'Visibility across the portfolio',
            'features' => [
                ['title' => 'Every project in one place', 'body' => 'Run each development as its own project with its own schedule, budget and team, all inside a single workspace you control.', 'route' => 'cpm-software'],
                ['title' => 'Cost &amp; budget across jobs', 'body' => 'Track BOQ, expenses and payments per project against budget, with every total recomputed on the server for consistency.', 'route' => 'services.cost'],
                ['title' => 'Schedule &amp; milestones', 'body' => 'Set milestones and dependencies per project and read programmes on a calendar and Gantt timeline to spot slippage early.', 'route' => 'services.planning'],
                ['title' => 'Progress from every site', 'body' => 'Site teams report status and percent complete from the field, so portfolio progress reflects reality, not last week.', 'route' => 'services.field'],
                ['title' => 'Reports for stakeholders', 'body' => 'Export project, cost and progress reports to CSV for investors, lenders and internal reviews whenever they are needed.', 'route' => 'features'],
            ],
            'band' => [
                'heading' => 'From groundbreaking to handover',
                'body' => 'Keep cost, schedule and progress aligned across every development, so a problem on one project is visible before it affects the portfolio.',
            ],
            'faqs' => [
                ['q' => 'Can I track several developments at once?', 'a' => 'Yes. Each development is a separate project with its own schedule, BOQ, budget and team, and you work across all of them from one workspace — which is exactly how developers use Best CPMS to keep a portfolio in view.'],
                ['q' => 'Can I see cost and progress across projects?', 'a' => 'Yes. Costs and progress are tracked per project and can be pulled as reports and exported to CSV, so you can review cost, schedule and completion across the portfolio rather than one job in isolation.'],
                ['q' => 'What does it cost to cover a development team?', 'a' => 'Pricing is per-user: :symbol:perUser per user, per month, minimum :minSeats users, so plans start from :symbol:fromMonth per month. A :trialDays-day free trial with no card lets you set up real projects first.'],
            ],
        ],

        /* ---------------------------------------------------------------- */
        /* Use-case: task management */
        /* ---------------------------------------------------------------- */
        'task-management' => [
            'slug' => 'construction-task-management-software',
            'keyword' => 'construction task management software',
            'breadcrumb' => 'Task Management',
            'kicker' => 'Solutions · Task management',
            'title' => 'Construction Task Management Software',
            'meta_description' => 'Construction task management software — break the build into tasks, assign them to the right people, set dependencies, and track status, percent complete and hours from site. Keep every crew clear on what is next. Free trial.',
            'meta_keywords' => 'construction task management software, construction task tracking, site task management app, construction to-do and task software, task assignment software for construction India',
            'h1' => 'Construction task management software',
            'lead' => 'Break the build into tasks, put them in the right hands, and watch them move. Best CPMS keeps every crew clear on what is next and shows you status and progress as the work happens.',
            'intro' => [
                'A construction programme is only as good as the tasks underneath it. When tasks live on whiteboards and in messages, nobody is sure what is assigned, what is blocked, or what is actually done. Best CPMS turns the programme into tracked tasks with owners, status and dependencies — so the next action is never in doubt.',
                'Tasks roll up into milestones and the overall schedule, and site staff update status, percent complete and actual hours from a phone. The result is a task list the office and the site both trust, and a programme that reflects real progress instead of good intentions.',
            ],
            'service_type' => 'Construction task management software',
            'features_heading' => 'Tasks, from assignment to done',
            'features' => [
                ['title' => 'Assign with clear owners', 'body' => 'Every task has an owner and a status, so there is no ambiguity about who is doing what or where a job currently stands.', 'route' => null],
                ['title' => 'Dependencies &amp; milestones', 'body' => 'Link tasks with dependencies and group them under milestones so the sequence of the build is explicit, not assumed.', 'route' => 'services.planning'],
                ['title' => 'Status from the field', 'body' => 'Site staff update task status, percent complete and actual hours from a phone, so the board reflects what is really happening.', 'route' => 'services.field'],
                ['title' => 'Task reports &amp; export', 'body' => 'Filter task status across a project or every job, and export to CSV for progress reviews and client updates.', 'route' => 'features'],
                ['title' => 'Role-based visibility', 'body' => 'Workers, supervisors and managers each see the tasks relevant to them, so the task list stays focused rather than overwhelming.', 'route' => null],
            ],
            'band' => [
                'heading' => 'Tasks that stay in sync with the plan',
                'body' => 'Because tasks feed the schedule and are updated from site, your programme is always current — close a task on site and the plan reflects it.',
            ],
            'faqs' => [
                ['q' => 'Can site workers update tasks from their phones?', 'a' => 'Yes. The web app is responsive and a companion field app lets workers view their assigned tasks and update status, percent complete and actual hours from site — so the task list reflects real progress without office data entry.'],
                ['q' => 'Can I set task dependencies and milestones?', 'a' => 'Yes. Tasks can be linked with dependencies and grouped under milestones, and the whole programme is readable on a calendar and Gantt timeline so the sequence of work is clear.'],
                ['q' => 'Is task management part of the standard plan?', 'a' => 'Yes. Task management is core to Best CPMS, included in the single per-user plan — :symbol:perUser per user per month, minimum :minSeats users — with a :trialDays-day free trial to start.'],
            ],
        ],

        /* ---------------------------------------------------------------- */
        /* Use-case: project tracking */
        /* ---------------------------------------------------------------- */
        'project-tracking' => [
            'slug' => 'construction-project-tracking-software',
            'keyword' => 'construction project tracking software',
            'breadcrumb' => 'Project Tracking',
            'kicker' => 'Solutions · Project tracking',
            'title' => 'Construction Project Tracking Software',
            'meta_description' => 'Construction project tracking software — follow schedule, cost and site progress in real time from one place, and catch slippage before it spreads. Updated from the field, trusted by the office. Free trial, no card.',
            'meta_keywords' => 'construction project tracking software, construction progress tracking, project status tracking construction, construction schedule and cost tracking, real-time project tracking India',
            'h1' => 'Construction project tracking software',
            'lead' => 'Follow schedule, cost and progress in real time from one place — and catch slippage while you can still do something about it. Best CPMS keeps the whole project honest, from plan to site.',
            'intro' => [
                'Tracking a project means knowing three things at once: where the schedule is, where the budget is, and what is actually built. When those live in different places, you only find out something slipped after it has already cost you. Best CPMS keeps schedule, cost and progress together, updated from the field, so the picture is current and the warning signs show early.',
                'The plan is tracked as milestones and tasks on a calendar and Gantt timeline; cost is tracked as BOQ, expenses and payments against budget; and progress comes straight from the people doing the work. One source of truth means the office and the site stop arguing about whose version is right.',
            ],
            'service_type' => 'Construction project tracking software',
            'features_heading' => 'Track the whole project, not just the plan',
            'features' => [
                ['title' => 'Schedule tracking', 'body' => 'Milestones, tasks and dependencies on a calendar and Gantt timeline make slippage visible as soon as it starts.', 'route' => 'services.planning'],
                ['title' => 'Cost vs budget', 'body' => 'Track BOQ, expenses and payments against budget, with totals recomputed on the server so the burn figure is trustworthy.', 'route' => 'services.cost'],
                ['title' => 'Progress from site', 'body' => 'Status, percent complete and actual hours come from the field, so tracked progress reflects reality rather than optimism.', 'route' => 'services.field'],
                ['title' => 'One source of truth', 'body' => 'Schedule, cost and progress live together, so everyone is tracking against the same current picture — not three stale copies.', 'route' => 'cpm-software'],
                ['title' => 'Export the status', 'body' => 'Pull project, cost and progress reports and export to CSV whenever someone needs the numbers in writing.', 'route' => 'features'],
            ],
            'band' => [
                'heading' => 'Catch slippage early',
                'body' => 'Because tracking data is live and comes from site, a schedule or cost problem shows up where it starts — early enough to act, not after the fact.',
            ],
            'faqs' => [
                ['q' => 'What can I track with Best CPMS?', 'a' => 'You track three things together: schedule (milestones, tasks and dependencies on a calendar and Gantt timeline), cost (BOQ, expenses and payments against budget), and site progress (status, percent complete and actual hours reported from the field).'],
                ['q' => 'Is the tracking data real-time?', 'a' => 'Progress is updated by the people doing the work, from site, so the tracked picture is as current as their last update rather than a weekly reconciliation. Cost totals are recomputed on the server as entries change.'],
                ['q' => 'How do I share project status with others?', 'a' => 'Project, cost and progress reports can be filtered and exported to CSV, so you can share a current status with clients, lenders or your own team without rebuilding it by hand.'],
            ],
        ],

        /* ---------------------------------------------------------------- */
        /* Use-case: reporting */
        /* ---------------------------------------------------------------- */
        'reporting' => [
            'slug' => 'construction-reporting-software',
            'keyword' => 'construction project reporting software',
            'breadcrumb' => 'Project Reporting',
            'kicker' => 'Solutions · Project reporting',
            'title' => 'Construction Project Reporting Software',
            'meta_description' => 'Construction project reporting software for project, task, cost, and progress reports you can export to CSV. Figures are computed on the server from the live job, for billing, audits, and client updates. Free trial.',
            'meta_keywords' => 'construction project reporting software, construction reporting software, construction progress report software, project cost report construction, CSV construction reports India',
            'h1' => 'Construction project reporting software',
            'lead' => 'Construction project reporting software that reads the live job: project, task, cost, and progress reports, each exportable to CSV. No more rebuilding the same spreadsheet every week.',
            'intro' => [
                'Reporting is where the week disappears: pulling numbers from different places, reconciling them, and formatting them into something you can send. It also breaks the moment the underlying data is stale. Best CPMS reports read straight from the live project, so the report is a view of current reality rather than a manual reconstruction of it.',
                'There are reports for projects, tasks, cost and progress, each filterable and each exportable to CSV. The figures are computed on the server from your own quantities, rates and entries — so the totals in a report you hand to a client or an auditor always add up to the same thing the system holds.',
            ],
            'service_type' => 'Construction reporting software',
            'features_heading' => 'Reports that write themselves',
            'features' => [
                ['title' => 'Project &amp; task reports', 'body' => 'Filterable status across one project or every job, so you can answer "where are we" without assembling it by hand.', 'route' => 'features'],
                ['title' => 'Cost reports', 'body' => 'BOQ, budget, expenses and payments summarised per project, with totals recomputed on the server for consistency.', 'route' => 'services.cost'],
                ['title' => 'Progress reports', 'body' => 'Completion and site progress drawn from the field, ready for the next client meeting or internal review.', 'route' => 'services.field'],
                ['title' => 'One-click CSV export', 'body' => 'Every report exports to CSV, so the numbers drop straight into billing, audits and your own spreadsheets.', 'route' => null],
                ['title' => 'Figures you can defend', 'body' => 'Amounts are computed server-side from your quantities and rates, never from the browser — so a report always matches the system.', 'route' => null],
            ],
            'band' => [
                'heading' => 'From live data to a sent report',
                'body' => 'Because reports read the current project rather than a copied spreadsheet, the status you send is the status that is true — and it took a click, not an afternoon.',
            ],
            'faqs' => [
                ['q' => 'What reports does Best CPMS include?', 'a' => 'Best CPMS includes project, task, cost and progress reports. Each can be filtered and exported to CSV, covering status across jobs, cost against budget, and completion drawn from site updates.'],
                ['q' => 'Can I export reports to use elsewhere?', 'a' => 'Yes. Every report exports to CSV in a click, so the data goes straight into billing, audit files or your own spreadsheets without re-keying anything.'],
                ['q' => 'Can I trust the totals in a report?', 'a' => 'Yes. Cost figures are recomputed on the server from your own quantities, rates and entries — the browser is never trusted for a total — so a report always matches the figures the system holds.'],
            ],
        ],

        /* ---------------------------------------------------------------- */
        /* Priority: construction management software */
        /* ---------------------------------------------------------------- */
        'management' => [
            'slug' => 'construction-management-software',
            'keyword' => 'construction management software',
            'breadcrumb' => 'Construction Management Software',
            'kicker' => 'Solutions · Construction management',
            'title' => 'Construction Management Software',
            'meta_description' => 'Construction management software for running several sites from one company account: programme, BOQ and budget, site progress, and reports. Role-based access, INR pricing, and a free trial.',
            'meta_keywords' => 'construction management software, construction management system, construction company software, multi site construction software, construction management software India',
            'h1' => 'Construction management software',
            'lead' => 'Run the company, not a pile of site files. Best CPMS is construction management software for the programme, the money, and the people on each job.',
            'intro' => [
                'Construction management falls apart when each site keeps its own sheet for dates, another for the bill of quantities, and a chat thread for what happened today. Construction management software in Best CPMS puts those records on the company account: every project has its schedule, its budget, and the updates that come back from site.',
                'Owners and admins invite the team and choose who can see cost. Managers and accountants work the budget, the BOQ, expenses, and payments. Supervisors and workers update the tasks they are assigned. A new company starts on a :trialDays-day free trial, then licenses seats at :symbol:perUser per user per month, minimum :minSeats users.',
            ],
            'service_type' => 'Construction management software',
            'features_heading' => 'One account for the sites you are running',
            'features' => [
                ['title' => 'Several projects, one company', 'body' => 'Each job keeps its own client, site, dates, tasks, budget, and bill of quantities. You move between them from the same workspace.', 'route' => 'cpm-software'],
                ['title' => 'A programme you can read', 'body' => 'Milestones and tasks carry dates. The calendar and the Gantt read those dates, so the schedule is the work, not a separate drawing.', 'route' => 'landing.scheduling'],
                ['title' => 'Money on the job', 'body' => 'The project budget, the BOQ, expenses, and payments stay together. Totals are recomputed on the server.', 'route' => 'landing.budget'],
                ['title' => 'Site updates the office can see', 'body' => 'Assigned people set status, percent complete, and hours from the field. The office is not waiting on a weekly sheet.', 'route' => 'services.field'],
                ['title' => 'Roles that match the company', 'body' => 'Owner, admin, manager, supervisor, accountant, and worker each open the screens that belong to that role.', 'route' => 'features'],
                ['title' => 'Reports for the review', 'body' => 'Project, task, cost, and progress reports export to CSV when a client or an auditor wants the file.', 'route' => 'landing.reporting'],
            ],
            'band' => [
                'heading' => 'Built for a contracting company, not a single spreadsheet',
                'body' => 'The same plan covers the programme, the bill of quantities, site progress, and the report. There is no per-project fee and no module to unlock later.',
            ],
            'faqs' => [
                ['q' => 'What is construction management software in Best CPMS?', 'a' => 'It is the company workspace for live jobs: projects, milestones and tasks, a bill of quantities and budget, expenses and payments, site progress, and reports. People sign in with a role, and they see the screens that role is allowed to open.'],
                ['q' => 'Can one company run more than one site?', 'a' => 'Yes. Each project keeps its own schedule, tasks, BOQ, and budget. There is no extra charge tied to the number of active projects.'],
                ['q' => 'How is construction management software priced?', 'a' => 'After a :trialDays-day free trial with no card, the plan is :symbol:perUser per user per month, minimum :minSeats users, from :symbol:fromMonth per month. Checkout in India charges Indian rupees through Razorpay.'],
            ],
        ],

        /* ---------------------------------------------------------------- */
        /* Priority: construction scheduling software */
        /* ---------------------------------------------------------------- */
        'scheduling' => [
            'slug' => 'construction-scheduling-software',
            'keyword' => 'construction scheduling software',
            'breadcrumb' => 'Scheduling Software',
            'kicker' => 'Solutions · Scheduling',
            'title' => 'Construction Scheduling Software',
            'meta_description' => 'Construction scheduling software for milestones, dated tasks, a month calendar, and a Gantt timeline. Each task can name one predecessor. Overdue work stays visible. Free trial, no card.',
            'meta_keywords' => 'construction scheduling software, construction project scheduling software, construction programme software, construction calendar software, construction timeline software',
            'h1' => 'Construction scheduling software',
            'lead' => 'Put the programme on the project: milestones, tasks with dates, a calendar, and a Gantt. Construction scheduling software in Best CPMS reads the dates you save, not a separate chart someone has to redraw.',
            'intro' => [
                'A construction schedule goes stale when the bar chart and the task list are two files. Construction scheduling software in Best CPMS stores milestones and tasks on the project. A task can have a start date and a due date, and it can name one other task as its predecessor. A task cannot depend on itself.',
                'The month calendar and the Gantt are views of those dates. The Gantt draws a bar when a task has a start date or a due date, and it can be filtered to one project. Work past its due date is marked overdue until the date or the status changes. Planning roles keep this programme; site roles update the tasks they are assigned.',
            ],
            'service_type' => 'Construction scheduling software',
            'features_heading' => 'A programme made of the dates on the job',
            'features' => [
                ['title' => 'Milestones with a status', 'body' => 'Each milestone belongs to a project and can be pending, in progress, completed, or delayed, with its own due date.', 'route' => 'landing.milestones'],
                ['title' => 'Tasks with start and due dates', 'body' => 'Dated tasks are what the calendar and the Gantt plot. A task with no dates stays off the timeline.', 'route' => 'services.planning'],
                ['title' => 'One predecessor', 'body' => 'A task can record a single predecessor so the sequence is stored with the work. The chart draws the bars; it does not draw dependency arrows.', 'route' => 'landing.gantt'],
                ['title' => 'Month calendar', 'body' => 'Tasks and milestones with a due date appear on the month view, for one project or the wider programme.', 'route' => null],
                ['title' => 'Overdue stays visible', 'body' => 'A task or milestone with a due date in the past, and not yet complete, is overdue. Reports can count those tasks per project.', 'route' => 'landing.project-tracking'],
                ['title' => 'The same dates the site updates', 'body' => 'When someone on site changes status or percent complete, the schedule and the progress report are looking at that task.', 'route' => 'services.field'],
            ],
            'band' => [
                'heading' => 'The chart and the task list are the same record',
                'body' => 'Change a date on the task and the calendar and the Gantt move with it. Nobody has to paste the programme into another file for the meeting.',
            ],
            'faqs' => [
                ['q' => 'What does construction scheduling software include?', 'a' => 'Milestones and tasks on each project, start and due dates, one predecessor per task, a month calendar, and a Gantt of dated tasks. Overdue items stay flagged while the due date is in the past and the work is not complete.'],
                ['q' => 'Does the Gantt show dependency arrows?', 'a' => 'No. The Gantt draws a bar from the task start date to the due date. The predecessor is stored on the task, and a task cannot depend on itself.'],
                ['q' => 'Is scheduling priced separately?', 'a' => 'No. Scheduling is included at :symbol:perUser per user per month, minimum :minSeats users, after a :trialDays-day free trial.'],
            ],
        ],

    ],

];
