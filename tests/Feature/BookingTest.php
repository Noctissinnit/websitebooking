<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class BookingTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function testCreateBooking(): void
    {
        $response = $this->post(route('bookings.store'), [
            '_token' => csrf_token(),
            'nis' => 111111,
            'password' => 'password',
            'room_id' => 1,
            'date' => '2024-10-24',
            'start_time' => '07:00',
            'end_time' => '09:00',
            'description' => 'Booking',
            'department_id' => 1,
            'users' => [2, 3, 4],
        ]);
        Log::debug($response->baseResponse);
        $response->assertStatus(302);
    }
}
