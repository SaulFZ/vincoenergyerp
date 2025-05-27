<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Test that home page redirects to login when not authenticated.
     */
    public function test_home_redirects_to_login_when_not_authenticated(): void
    {
        $response = $this->get('/');

        // Probamos que efectivamente redirige al login
        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }

    /**
     * Test that login page loads successfully.
     */
    public function test_login_page_loads_successfully(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }
}
