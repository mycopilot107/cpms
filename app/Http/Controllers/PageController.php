<?php

namespace App\Http\Controllers;

use App\Support\Stories;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Public marketing, informational and legal pages. Every method is a thin
 * view return — these pages hold no tenant data and are reachable by guests.
 * (The Contact and Feedback forms live in ContactController.)
 */
class PageController extends Controller
{
    /** Homepage. Signed-in users are sent straight to their dashboard. */
    public function home(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        // Featured approved customer story (falls back to sample content in
        // the view when none has been approved yet).
        return view('public.home', ['story' => Stories::spotlight()]);
    }

    /**
     * Primary SEO landing page targeting the head keyword
     * ("construction project management software / system"). Linked from the
     * nav + footer and listed first in the sitemap after the homepage.
     */
    public function cpmSoftware(): View
    {
        return view('public.cpm-software');
    }

    // -- Company ----------------------------------------------------------
    public function about(): View
    {
        return view('public.about');
    }

    public function mission(): View
    {
        return view('public.mission');
    }

    public function team(): View
    {
        return view('public.team');
    }

    public function careers(): View
    {
        return view('public.careers');
    }

    // -- Product ----------------------------------------------------------
    public function features(): View
    {
        return view('public.features');
    }

    public function pricing(): View
    {
        return view('public.pricing');
    }

    public function reviews(): View
    {
        return view('public.reviews', [
            'stories' => Stories::approved(),
            'average' => Stories::averageRating(),
            'storyCount' => Stories::count(),
        ]);
    }

    // -- Services ---------------------------------------------------------
    public function servicePlanning(): View
    {
        return view('public.services.planning');
    }

    public function serviceCost(): View
    {
        return view('public.services.cost');
    }

    public function serviceField(): View
    {
        return view('public.services.field');
    }

    // -- Resources --------------------------------------------------------
    public function blog(): View
    {
        return view('public.blog', [
            'posts' => config('blog.posts', []),
        ]);
    }

    /** One article from config/blog.php. The slug is the config key, not free text used in a query. */
    public function blogPost(string $slug): View
    {
        $post = config("blog.posts.$slug");

        abort_unless(is_array($post), 404);

        return view('public.blog-post', [
            'slug' => $slug,
            'post' => $post,
        ]);
    }

    public function caseStudies(): View
    {
        return view('public.case-studies');
    }

    public function faq(): View
    {
        return view('public.faq');
    }

    public function help(): View
    {
        return view('public.help');
    }

    // -- Legal ------------------------------------------------------------
    public function privacy(): View
    {
        return view('public.legal.privacy');
    }

    public function terms(): View
    {
        return view('public.legal.terms');
    }

    public function refund(): View
    {
        return view('public.legal.refund');
    }

    public function cookies(): View
    {
        return view('public.legal.cookies');
    }
}
