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
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_the_old_duplicate_admin_login_page_is_gone(): void
    {
        $this->get('/admin/login')->assertNotFound();
        $this->post('/admin/login-submit')->assertNotFound();
    }
}
