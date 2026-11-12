<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * The pages a role can be allowed to open. Dashboard is not listed: every
 * active user can open it, so a role is never locked out of the home page.
 */
class AppPages
{
    /**
     * Page key => label shown in the menu and on the role form.
     *
     * @var array<string, string>
     */
    public const LABELS = [
        'projects' => 'Projects',
        'tasks' => 'Tasks',
        'planning' => 'Planning',
        'daily_reports' => 'Daily reports',
        'finance' => 'Finance',
        'reports' => 'Reports',
        'team' => 'Team',
        'company' => 'Company',
        'billing' => 'Billing',
        'stories' => 'Share your story',
    ];

    /**
     * Page key => route name patterns (Str::is).
     *
     * @var array<string, list<string>>
     */
    public const ROUTES = [
        'projects' => ['projects.*', 'share-links.*'],
        'tasks' => ['tasks.*', 'task-photos.*'],
        'planning' => ['planning.*', 'milestones.*'],
        'daily_reports' => ['daily-reports.*', 'daily-report-photos.*'],
        'finance' => ['finance.*', 'boq-items.*', 'expenses.*', 'project-payments.*'],
        'reports' => ['reports.*'],
        'team' => ['users.*'],
        'company' => ['company.*'],
        'billing' => ['subscription.plans', 'subscription.checkout', 'subscription.verify'],
        'stories' => ['stories.*'],
    ];

    /**
     * Page key => [route name used by the menu link, route patterns that mark it active].
     *
     * @var array<string, array{route: string, active: list<string>}>
     */
    public const LINKS = [
        'projects' => ['route' => 'projects.index', 'active' => ['projects.*', 'share-links.*']],
        'tasks' => ['route' => 'tasks.index', 'active' => ['tasks.*', 'task-photos.*']],
        'planning' => ['route' => 'planning.calendar', 'active' => ['planning.*', 'milestones.*']],
        'daily_reports' => ['route' => 'daily-reports.index', 'active' => ['daily-reports.*', 'daily-report-photos.*']],
        'finance' => ['route' => 'finance.index', 'active' => ['finance.*', 'boq-items.*', 'expenses.*', 'project-payments.*']],
        'reports' => ['route' => 'reports.index', 'active' => ['reports.*']],
        'team' => ['route' => 'users.index', 'active' => ['users.*']],
        'company' => ['route' => 'company.settings', 'active' => ['company.*']],
        'billing' => ['route' => 'subscription.plans', 'active' => ['subscription.plans', 'subscription.checkout', 'subscription.verify']],
        'stories' => ['route' => 'stories.index', 'active' => ['stories.*']],
    ];

    /**
     * Pages the built-in roles open today, so turning on page checks does not
     * change who sees which menu.
     *
     * @return list<string>
     */
    public static function forSlug(string $slug): array
    {
        $work = ['projects', 'tasks', 'planning', 'daily_reports', 'stories'];
        $finance = ['finance', 'reports'];
        $office = ['team', 'company', 'billing'];

        return match ($slug) {
            'owner', 'admin' => array_merge($work, $finance, $office),
            'manager', 'accountant' => array_merge($work, $finance),
            default => $work,
        };
    }

    /**
     * @return list<string>
     */
    public static function keys(): array
    {
        return array_keys(self::LABELS);
    }

    public static function pageForRoute(?string $name): ?string
    {
        if ($name === null || $name === '' || $name === 'dashboard') {
            return $name === 'dashboard' ? 'dashboard' : null;
        }

        foreach (self::ROUTES as $page => $patterns) {
            foreach ($patterns as $pattern) {
                if (Str::is($pattern, $name)) {
                    return $page;
                }
            }
        }

        return null;
    }
}
