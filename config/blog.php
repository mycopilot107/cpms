<?php

/*
| Blog articles. Slugs are the URL segment under /blog/{slug}.
| Keep this file cacheable: scalars and arrays only, no closures.
| Body paragraphs are plain text. related_route is a named route.
*/

return [

    'posts' => [

        'programme-and-budget' => [
            'title' => 'Why the site programme and the budget drift apart',
            'category' => 'Playbook',
            'date' => 'September 2026',
            'minutes' => 6,
            'excerpt' => 'The programme says one thing and the cost report says another. The gap usually starts because the dates and the money live in different files.',
            'meta_description' => 'Why a construction programme and budget drift apart, and how keeping tasks, the bill of quantities, expenses, and payments on the same project closes the gap.',
            'related_route' => 'landing.budget',
            'related_label' => 'Construction budget management software',
            'body' => [
                'On most jobs the programme is a chart and the budget is a workbook. They are updated by different people, on different days, and nobody notices they have diverged until a review. The site has poured a slab the chart still shows as next month, or the chart shows finishes underway while the cost sheet has not recorded the material.',
                'The drift is not a discipline problem first. It is a storage problem. If the task dates, the bill of quantities, the expenses, and the payments are separate files, each one can be “right” and the job can still be wrong.',
                'Put those records on the same project. A task with a start and a due date is what the Gantt draws. A BOQ line is quantity times rate, calculated on the server. An expense has a category and a date. A payment is incoming or outgoing. The cost report then reads those records instead of a pasted summary.',
                'A practical check, once a week, is short. Open the project. Look at milestones marked delayed. Look at expenses recorded since the last check. Look at payments in against what the programme says is complete. If those three disagree, the disagreement is visible while the job can still be corrected.',
                'Best CPMS keeps that set in one workspace: planning for dates, finance for the BOQ, expenses, and payments, and reports that export to CSV. The programme and the budget drift when they are copies. They stay closer when they are the same project.',
            ],
        ],

        'building-a-boq' => [
            'title' => 'A practical guide to a bill of quantities people will use',
            'category' => 'Cost control',
            'date' => 'August 2026',
            'minutes' => 8,
            'excerpt' => 'A BOQ fails when it is too vague to price or too detailed to maintain. Here is a line structure that survives a live project.',
            'meta_description' => 'How to structure a construction bill of quantities with codes, units, quantities, and rates so the line amount stays quantity times rate.',
            'related_route' => 'landing.boq',
            'related_label' => 'Construction BOQ software',
            'body' => [
                'A bill of quantities is useful when someone other than its author can find a line and trust the amount. That means a code, a short description, a unit, a quantity, and a rate. The amount should be the product of the quantity and the rate, not a number typed beside them.',
                'Codes matter more than prose. “Brickwork, external, ground floor” is hard to scan in a list of two hundred lines. A code plus a category lets the commercial team filter to one trade. Keep the description specific enough to know what was priced, and short enough that a site engineer recognises it.',
                'Units have to match how the work is bought. Square metres, cubic metres, numbers, and running metres should not be mixed on one line. If the unit is wrong, every later quantity update multiplies the error.',
                'Do not hide contingency inside a rate you cannot explain. A separate line, with its own quantity and rate, is easier to reduce when the risk does not happen. The same applies to provisional sums: they are lines, not notes under the total.',
                'In Best CPMS each of those lines belongs to one project. The server stores the amount as quantity times rate. Expenses and payments on that project are the spend and the cash beside the bill, which is what the cost report exports.',
            ],
        ],

        'reading-a-gantt-chart' => [
            'title' => 'Reading a construction Gantt chart without lying to yourself',
            'category' => 'Scheduling',
            'date' => 'August 2026',
            'minutes' => 5,
            'excerpt' => 'A bar on a chart is only as true as the start and due dates behind it. Here is what to check before you treat the picture as the plan.',
            'meta_description' => 'How to read a construction Gantt chart built from task start and due dates, including what a today marker shows and what a predecessor does not draw.',
            'related_route' => 'landing.gantt',
            'related_label' => 'Construction Gantt chart software',
            'body' => [
                'A Gantt chart looks decisive. A bar crossing three months feels like a commitment. It is only a drawing of two dates: when the task starts and when it is due. If those dates were guessed, the bar is a guess with better graphics.',
                'Before a review, check three things. Does every bar you care about have both a start and a due date, or did one of them default because only one date was entered? Is the today line inside the window, and are bars that should already be finished still open? Is the chart filtered to the project you are discussing, or are you looking at every job at once?',
                'A predecessor on a task records that one task depends on another. That is a fact about the sequence. It is not, by itself, a critical-path calculation, and the Best CPMS chart does not draw arrows between bars. If you need the sequence, open the task. If you need the dates, read the bar.',
                'Milestones are the dates other people will remember. Use them for the points that end a stage, and use tasks for the work between those points. A chart with only milestones is too coarse. A chart with only tasks hides the dates the client will ask about.',
                'Update the dates when the site changes them. A chart that is rebuilt from current task dates is allowed to look worse than last week’s export. That is the version worth taking into the meeting.',
            ],
        ],

        'site-progress-updates' => [
            'title' => 'Getting site engineers to update progress',
            'category' => 'Field',
            'date' => 'July 2026',
            'minutes' => 4,
            'excerpt' => 'Progress stays blank when the update is a favour to the office. It gets filled in when the task on the phone is the task the engineer is actually doing.',
            'meta_description' => 'How site engineers update construction task status, percent complete, and hours when the task list is assigned to them.',
            'related_route' => 'services.field',
            'related_label' => 'Field and progress tracking',
            'body' => [
                'Asking a site engineer to “update the sheet” at the end of the day competes with everything else that has to be closed before they leave. The update happens when it is the same list they use to see what they were assigned.',
                'Give each person tasks with an owner. A shared pool that says “the team” belongs to nobody, so nobody updates it. Status, percent complete, and actual hours are enough for a progress picture. A long form will not be filled in from a noisy floor.',
                'Best CPMS lets an assigned user open their tasks in the web app or the field API and post progress. Managers can see tasks they did not personally receive. Workers do not need the budget screen to report that a task moved.',
                'Agree what the percent means before the job starts. “50” should mean half the quantity, or half the area, not “we have been here for half the planned time”. If the meaning changes by person, the progress report cannot be compared across tasks.',
                'Look at the updates in the same week they are made. An engineer who reports progress and never hears it used will stop. The point of the update is the Gantt, the milestone status, and the progress report — not a folder of screenshots.',
            ],
        ],

        'month-end-reporting' => [
            'title' => 'A month-end reporting routine that fits in an afternoon',
            'category' => 'Reporting',
            'date' => 'July 2026',
            'minutes' => 7,
            'excerpt' => 'Month-end takes days when the numbers are reassembled by hand. It takes an afternoon when the reports read the project that was updated all month.',
            'meta_description' => 'A month-end routine for construction project, task, cost, and progress reports, exported to CSV from the live job.',
            'related_route' => 'landing.reporting',
            'related_label' => 'Construction project reporting software',
            'body' => [
                'Month-end overruns when someone has to collect task status from site, expenses from accounts, and a programme from a planner, then make them agree. The collection is the work. The report is the easy part once the records exist.',
                'During the month, the useful habit is small. Tasks get a status and a percent. Expenses get a project and a category when they are incurred. Payments get a direction and a method. Milestones that slipped are marked delayed instead of being left on the old date.',
                'At month-end, open the four reports: projects, tasks, cost, and progress. Filter to the jobs in the review. Export each one to CSV if someone outside the system needs the file. The totals in the cost report come from the BOQ, expenses, and payments already stored, recomputed on the server.',
                'Do not add a fifth spreadsheet that “fixes” the export. If a number looks wrong, fix the task, the expense, or the payment. The next export then matches the correction, and next month does not start from a private workbook.',
                'That routine is what construction project reporting in Best CPMS is for. The reports are views of the projects, not a separate publishing step.',
            ],
        ],

    ],

];
