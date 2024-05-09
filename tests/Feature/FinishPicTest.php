<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class FinishPicTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_finish_pic_with_event(): void
    {
        $response = $this->post('/api/set2DpicFinish', [
            'id' => 53,
        ]);

        $response->dump();

        //$response->assertStatus(200);
    }
}
