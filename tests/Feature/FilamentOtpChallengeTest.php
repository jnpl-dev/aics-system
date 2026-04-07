<?php

namespace Tests\Feature;

use App\Filament\Pages\Auth\OtpChallenge;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class FilamentOtpChallengeTest extends TestCase
{
    public function test_otp_page_redirects_to_login_when_challenge_is_missing(): void
    {
        session()->forget('filament_login_otp_challenge_id');

        $response = $this->get('/otp');

        $response
            ->assertRedirect(route('login'))
            ->assertSessionHas('filament_auth_error', [
                'field' => 'data.email',
                'message' => 'OTP session expired. Please sign in again.',
            ]);
    }

    public function test_updated_otp_digits_handles_null_key_without_crashing(): void
    {
        $challengeId = 'test-otp-challenge-id';

        session()->put('filament_login_otp_challenge_id', $challengeId);

        Cache::put(
            "filament-login-otp:{$challengeId}",
            [
                'user_id' => 1,
                'email' => 'staff@example.com',
                'code_hash' => Hash::make('123456'),
                'otp_sent' => true,
                'attempts' => 0,
                'remember' => false,
            ],
            now()->addMinutes(10),
        );

        Livewire::test(OtpChallenge::class)
            ->set('otpDigits', ['9', '', '', '', '', ''])
            ->assertSet('otpDigits.0', '9');
    }
}
