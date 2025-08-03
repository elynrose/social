<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BasicTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function application_returns_200_for_home_page()
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    /** @test */
    public function database_connection_works()
    {
        // Test that we can access the database
        $this->assertTrue(\Schema::hasTable('users'));
    }

    /** @test */
    public function artisan_command_works()
    {
        $this->artisan('list')->assertExitCode(0);
    }
} 