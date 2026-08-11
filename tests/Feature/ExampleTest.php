<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_login_page_is_available(): void
    {
        $response = $this->get('/admin/login');

        $response->assertStatus(200);
    }
}
