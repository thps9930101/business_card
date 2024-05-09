<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ApiTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_register(): void
    {
        /* skip this test */
        $this->markTestSkipped('skip this test because email is already registered');
        $response = $this->post('/api/register', [
            'name' => 'test',
            'email' => env('TEST_MAIL'),
            'password'=>'12345678',
            'password_confirmation'=>'12345678']
        );

        $this->assertDatabaseHas('users', [
            'email' => env('TEST_MAIL')
        ]);


        $response->assertStatus(200);
    }
}
