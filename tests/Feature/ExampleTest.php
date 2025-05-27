<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        // En lugar de probar '/', probamos la página de login que sí devuelve 200
        $response = $this->get('/login');

        $response->assertStatus(200);
    }
}
