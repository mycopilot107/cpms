<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PriorityKeywordPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_priority_keyword_pages_are_indexable(): void
    {
        $pages = [
            '/construction-management-software' => 'Construction management software',
            '/construction-scheduling-software' => 'Construction scheduling software',
            '/construction-project-management-software-india' => 'Construction project management software India',
            '/construction-project-tracking-software' => 'Construction project tracking software',
            '/construction-budget-management-software' => 'Construction budget management software',
            '/construction-expense-tracking-software' => 'Construction expense tracking software',
            '/construction-reporting-software' => 'Construction project reporting software',
            '/construction-boq-software' => 'Construction BOQ software',
            '/construction-gantt-chart-software' => 'Construction Gantt chart software',
            '/construction-milestone-tracking-software' => 'Construction milestone tracking software',
            '/construction-payment-tracking-software' => 'Construction payment tracking software',
            '/blog/building-a-boq' => 'A practical guide to a bill of quantities people will use',
        ];

        foreach ($pages as $path => $heading) {
            $this->get($path)
                ->assertOk()
                ->assertSee('<h1', false)
                ->assertSee($heading, false)
                ->assertSee('<link rel="canonical"', false);
        }

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertSee('construction-management-software', false)
            ->assertSee('construction-scheduling-software', false)
            ->assertSee('construction-project-management-software-india', false)
            ->assertSee('construction-budget-management-software', false)
            ->assertSee('construction-expense-tracking-software', false)
            ->assertSee('construction-boq-software', false)
            ->assertSee('blog/building-a-boq', false);

        $this->get('/blog/not-a-real-article')->assertNotFound();

        $this->get('/')
            ->assertOk()
            ->assertSee('construction-budget-management-software', false)
            ->assertSee('construction-gantt-chart-software', false);
    }
}
