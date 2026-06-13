<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class SiteControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // These pages render the full Inertia root view, which pulls in Vite;
        // stub it so the tests don't depend on a built asset manifest.
        $this->withoutVite();
    }

    public function test_index_renders_the_welcome_page(): void
    {
        $this->get(route('site.index'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Welcome')
                ->has('canLogin')
                ->has('canRegister')
            );
    }

    public function test_plans_page_shows_the_guest_variant_to_visitors(): void
    {
        $this->get(route('site.plans'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->component('Plans'));
    }

    public function test_plans_page_shows_the_authenticated_variant_to_logged_in_users(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('site.plans'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->component('AuthPlans'));
    }

    public function test_contact_page_renders(): void
    {
        $this->get(route('site.contact'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->component('Contact'));
    }
}