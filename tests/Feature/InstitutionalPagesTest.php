<?php

namespace Tests\Feature;

use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class InstitutionalPagesTest extends TestCase
{
    public function test_the_regulation_page_is_available(): void
    {
        $this->get(route('event.regulation'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Event/Regulation'));
    }

    public function test_the_schedule_page_is_available(): void
    {
        $this->get(route('event.schedule'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Event/Schedule')
                ->has('registrationStartsAt')
                ->has('registrationEndsAt'));
    }

    public function test_the_contact_page_is_available(): void
    {
        $this->get(route('event.contact'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Event/Contact'));
    }
}
