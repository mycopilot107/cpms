<?php

use App\Http\Controllers\AdminStoryController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\BoqItemController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DownloadController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\MilestoneController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PlanningController;
use App\Http\Controllers\PlatformController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectPaymentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\StoryController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public marketing / informational / legal pages
|--------------------------------------------------------------------------
| Reachable by everyone (no auth, no subscription gate). The homepage serves
| the marketing page to guests and redirects signed-in users to the dashboard.
*/
Route::get('/', [PageController::class, 'home'])->name('home');

// Primary SEO landing page (head keyword: construction project management software/system)
Route::get('construction-project-management-software', [PageController::class, 'cpmSoftware'])->name('cpm-software');

// SEO — dynamic sitemap + robots (always reflect APP_URL)
Route::get('sitemap.xml', [SitemapController::class, 'sitemap'])->name('sitemap');
Route::get('robots.txt', [SitemapController::class, 'robots'])->name('robots');

// Display-currency switch (INR / USD). A display preference stored in a
// cookie — a GET link is fine; unsupported codes are ignored by the
// controller. Constrained to 3 letters so it never shadows other routes.
Route::get('currency/{code}', [CurrencyController::class, 'switch'])
    ->where('code', '[A-Za-z]{3}')
    ->name('currency.switch');

// Company
Route::get('about', [PageController::class, 'about'])->name('about');
Route::get('mission-and-vision', [PageController::class, 'mission'])->name('mission');
Route::get('team', [PageController::class, 'team'])->name('team');
Route::get('careers', [PageController::class, 'careers'])->name('careers');

// Product
Route::get('features', [PageController::class, 'features'])->name('features');
Route::get('pricing', [PageController::class, 'pricing'])->name('pricing');
Route::get('reviews', [PageController::class, 'reviews'])->name('reviews');

// Services
Route::get('services/planning-scheduling', [PageController::class, 'servicePlanning'])->name('services.planning');
Route::get('services/cost-control', [PageController::class, 'serviceCost'])->name('services.cost');
Route::get('services/field-tracking', [PageController::class, 'serviceField'])->name('services.field');

// SEO "Solutions" — keyword landing pages (by audience + use-case), data-driven
// from config/landing.php. One named route per key, registered with
// controller-action syntax (NOT closures) so `php artisan route:cache` works.
// The controller resolves which page to render from the matched route name.
foreach (array_keys(config('landing.pages', [])) as $landingKey) {
    Route::get(config("landing.pages.$landingKey.slug"), [LandingController::class, 'show'])
        ->name('landing.'.$landingKey);
}

// Mobile app — public landing page + the Android APK download. The APK is
// produced locally (see mobile/build_apk.sh) and dropped into public/downloads;
// the controller serves it if present and degrades gracefully if not.
Route::get('get-the-app', [DownloadController::class, 'app'])->name('app.download-page');
Route::get('download/android', [DownloadController::class, 'android'])->name('app.download');

// Resources
Route::get('blog', [PageController::class, 'blog'])->name('blog');
Route::get('blog/{slug}', [PageController::class, 'blogPost'])
    ->where('slug', '[a-z0-9\-]+')
    ->name('blog.show');
Route::get('case-studies', [PageController::class, 'caseStudies'])->name('case-studies');
Route::get('faq', [PageController::class, 'faq'])->name('faq');
Route::get('help', [PageController::class, 'help'])->name('help');
Route::get('how-it-works', [PageController::class, 'howItWorks'])->name('how-it-works');

// Legal — exact URLs requested
Route::get('privacy-policy', [PageController::class, 'privacy'])->name('privacy');
Route::get('terms-and-conditions', [PageController::class, 'terms'])->name('terms');
Route::get('refund-policy', [PageController::class, 'refund'])->name('refund');
Route::get('cookie-policy', [PageController::class, 'cookies'])->name('cookies');

// Contact + customer feedback (persist to DB, email shobha.solanki107@gmail.com).
// Throttled to blunt spam/abuse of the public POST endpoints.
Route::get('contact', [ContactController::class, 'contact'])->name('contact');
Route::post('contact', [ContactController::class, 'storeContact'])
    ->middleware('throttle:10,1')->name('contact.store');
Route::get('feedback', [ContactController::class, 'feedback'])->name('feedback');
Route::post('feedback', [ContactController::class, 'storeFeedback'])
    ->middleware('throttle:10,1')->name('feedback.store');

/*
|--------------------------------------------------------------------------
| Guest routes
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store'])->name('register.store');

    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store'])->name('login.store');

    /*
     | Forgot / reset password. Guest-only. The two POST endpoints are
     | throttled, and `forgot-password` replies identically whether or not the
     | email exists so it can't be used to enumerate accounts. The reset link
     | route is named `password.reset` because the framework's reset
     | notification builds its URL from that exact route name.
     */
    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->middleware('throttle:6,1')->name('password.email');
    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->middleware('throttle:6,1')->name('password.store');

    /*
     | Social login (Google / Facebook), OAuth 2.0 authorization-code flow.
     | `redirect` sends the user to the provider; `callback` receives the code.
     | The provider is constrained so only the two we support can be requested.
     */
    Route::get('auth/{provider}/redirect', [SocialAuthController::class, 'redirect'])
        ->whereIn('provider', ['google', 'facebook'])
        ->name('social.redirect');
    Route::get('auth/{provider}/callback', [SocialAuthController::class, 'callback'])
        ->whereIn('provider', ['google', 'facebook'])
        ->name('social.callback');
});

/*
|--------------------------------------------------------------------------
| Authenticated routes
|--------------------------------------------------------------------------
| logout and the trial-expired page must stay OUTSIDE the "subscription"
| gate, otherwise an expired tenant would be caught in a redirect loop.
*/
Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::view('subscription/expired', 'subscription.expired')->name('subscription.expired');

    /*
     | Billing / checkout. These MUST stay outside the "subscription" gate:
     | an expired company needs to reach them, and gating them would create a
     | redirect loop back to the expired page. Access is limited to
     | owners/admins inside the controller.
     */
    Route::get('subscription/plans', [SubscriptionController::class, 'plans'])->name('subscription.plans');
    Route::post('subscription/checkout', [SubscriptionController::class, 'checkout'])->name('subscription.checkout');
    Route::post('subscription/verify', [SubscriptionController::class, 'verify'])->name('subscription.verify');

    /*
     | Success-story moderation — site administrator only (gated by the
     | `story-admin` middleware to config('stories.admin_email')). Kept OUTSIDE
     | the subscription gate so moderation still works regardless of the
     | admin's own company trial state.
     */
    Route::middleware('story-admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('stories', [AdminStoryController::class, 'index'])->name('stories.index');
        Route::patch('stories/{story}/approve', [AdminStoryController::class, 'approve'])->name('stories.approve');
        Route::patch('stories/{story}/reject', [AdminStoryController::class, 'reject'])->name('stories.reject');
        Route::patch('stories/{story}/feature', [AdminStoryController::class, 'feature'])->name('stories.feature');
        Route::delete('stories/{story}', [AdminStoryController::class, 'destroy'])->name('stories.destroy');
    });

    /*
      | Platform console — super admins only (gated by the `super-admin`
      | middleware and users.is_admin). This is the one area
     | that crosses the tenant boundary: it lists every company and its
     | subscription expiry. Read-only, and kept OUTSIDE the subscription gate so
     | a super admin can always reach it regardless of their own company state.
     */
    Route::middleware('super-admin')->prefix('platform')->name('platform.')->group(function () {
        Route::get('companies', [PlatformController::class, 'index'])->name('companies.index');
        Route::get('companies/{company}', [PlatformController::class, 'show'])->name('companies.show');
    });

    // Main application — requires an active trial or subscription.
    Route::middleware('subscription')->group(function () {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::resource('projects', ProjectController::class);
        Route::resource('tasks', TaskController::class);
        Route::resource('users', UserController::class)->except(['show']);

        /*
         | Planning — calendar + Gantt (all active users) and milestones CRUD
         | (gated to planning roles inside the controller/policy).
         */
        Route::get('planning/calendar', [PlanningController::class, 'calendar'])->name('planning.calendar');
        Route::get('planning/gantt', [PlanningController::class, 'gantt'])->name('planning.gantt');
        Route::resource('milestones', MilestoneController::class)->except(['show']);

        /*
         | Finance — dashboard plus BOQ, expenses and project payments.
         | Resource parameter names are pinned so they match the camelCase
         | route-model keys used by the form requests' authorize() checks
         | (default would be {boq_item} / {project_payment}).
         */
        Route::get('finance', [FinanceController::class, 'index'])->name('finance.index');
        Route::resource('boq-items', BoqItemController::class)
            ->except(['show'])
            ->parameters(['boq-items' => 'boqItem']);
        Route::resource('expenses', ExpenseController::class)->except(['show']);
        Route::resource('project-payments', ProjectPaymentController::class)
            ->except(['show'])
            ->parameters(['project-payments' => 'projectPayment']);

        /*
         | Reports — hub + four report views, each with ?export=csv.
         | Gated to finance/report roles inside the controller.
         */
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/projects', [ReportController::class, 'projects'])->name('reports.projects');
        Route::get('reports/tasks', [ReportController::class, 'tasks'])->name('reports.tasks');
        Route::get('reports/cost', [ReportController::class, 'cost'])->name('reports.cost');
        Route::get('reports/progress', [ReportController::class, 'progress'])->name('reports.progress');

        /*
         | Success stories — a signed-in member shares their story from here;
         | it queues for admin approval before appearing on the public site.
         */
        Route::get('stories', [StoryController::class, 'index'])->name('stories.index');
        Route::post('stories', [StoryController::class, 'store'])->name('stories.store');
    });
});
