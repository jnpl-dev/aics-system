<?php

namespace App\Filament\Pages\Auth;

use Filament\Facades\Filament;
use Filament\Models\Contracts\FilamentUser;
use Filament\Notifications\Notification;
use Filament\Pages\SimplePage;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class OtpChallenge extends SimplePage
{
    private const OTP_TTL_MINUTES = 10;

    private const OTP_MAX_ATTEMPTS = 5;

    private const OTP_CHALLENGE_SESSION_KEY = 'filament_login_otp_challenge_id';

    private const LOGIN_ROUTE_NAME = 'login';

    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'filament.pages.auth.otp-challenge';

    /**
     * @var array<int, string>
     */
    public array $otpDigits = ['', '', '', '', '', ''];

    public bool $otpSent = false;

    public function mount(): void
    {
        if (Filament::auth()->check()) {
            redirect()->intended(Filament::getUrl());

            return;
        }

        $challengeId = $this->getChallengeIdFromSession();

        if (blank($challengeId) || ! is_array(Cache::get($this->otpCacheKey($challengeId)))) {
            $this->clearChallenge((string) $challengeId);

            redirect()->route(self::LOGIN_ROUTE_NAME)->withErrors([
                'otp_code' => 'OTP session expired. Please sign in again.',
            ]);

            return;
        }

        $payload = Cache::get($this->otpCacheKey($challengeId));

        $this->otpSent = is_array($payload)
            && (($payload['otp_sent'] ?? false) === true)
            && is_string($payload['code_hash'] ?? null)
            && filled($payload['code_hash']);
    }

    public function getHeading(): string
    {
        return 'OTP Authentication';
    }

    public function getSubheading(): ?string
    {
        return 'Please enter the OTP code sent to your email address.';
    }

    public function verifyOtp(): mixed
    {
        $otpCode = preg_replace('/\D+/', '', implode('', $this->otpDigits));

        if (strlen((string) $otpCode) !== 6) {
            $this->addError('otp_code', 'Please enter the complete 6-digit OTP code.');

            return null;
        }

        $challengeId = $this->getChallengeIdFromSession();

        if (blank($challengeId)) {
            return redirect()->route(self::LOGIN_ROUTE_NAME)->withErrors([
                'otp_code' => 'OTP session expired. Please sign in again.',
            ]);
        }

        $payload = Cache::get($this->otpCacheKey($challengeId));

        if (! is_array($payload)) {
            $this->clearChallenge($challengeId);

            return redirect()->route(self::LOGIN_ROUTE_NAME)->withErrors([
                'otp_code' => 'OTP session expired. Please sign in again.',
            ]);
        }

        $attempts = (int) ($payload['attempts'] ?? 0);

        if (! is_string($payload['code_hash'] ?? null) || blank($payload['code_hash'])) {
            $this->addError('otp_code', 'Your verification code is still being sent. Please wait a moment.');

            return null;
        }

        if ($attempts >= self::OTP_MAX_ATTEMPTS) {
            $this->clearChallenge($challengeId);

            return redirect()->route(self::LOGIN_ROUTE_NAME)->withErrors([
                'otp_code' => 'Maximum OTP attempts exceeded. Please sign in again.',
            ]);
        }

        if (! Hash::check((string) $otpCode, (string) ($payload['code_hash'] ?? ''))) {
            $payload['attempts'] = $attempts + 1;

            Cache::put(
                $this->otpCacheKey($challengeId),
                $payload,
                now()->addMinutes(self::OTP_TTL_MINUTES)
            );

            $this->addError('otp_code', 'Invalid OTP code. Please try again.');

            return null;
        }

        $authGuard = Filament::auth();
        $authProvider = $authGuard->getProvider(); /** @phpstan-ignore-line */

        $user = $authProvider->retrieveById($payload['user_id'] ?? null);

        if (! $user instanceof Authenticatable) {
            $this->clearChallenge($challengeId);

            return redirect()->route(self::LOGIN_ROUTE_NAME)->withErrors([
                'email' => 'These credentials do not match an active account.',
            ]);
        }

        if ($user instanceof FilamentUser && (! $user->canAccessPanel(Filament::getCurrentOrDefaultPanel()))) {
            $this->clearChallenge($challengeId);

            return redirect()->route(self::LOGIN_ROUTE_NAME)->withErrors([
                'email' => 'These credentials do not match an active account.',
            ]);
        }

        $remember = (bool) ($payload['remember'] ?? false);

        $authGuard->login($user, $remember);

        $this->clearChallenge($challengeId);

        session()->regenerate();

        Notification::make()
            ->title('OTP verified')
            ->body('Signed in successfully.')
            ->success()
            ->send();

        return redirect()->intended(Filament::getUrl());
    }

    public function resendOtp(): void
    {
        if (! $this->otpSent) {
            return;
        }

        $challengeId = $this->getChallengeIdFromSession();

        if (blank($challengeId)) {
            $this->addError('otp_code', 'OTP session expired. Please sign in again.');

            return;
        }

        $payload = Cache::get($this->otpCacheKey($challengeId));

        if (! is_array($payload)) {
            $this->clearChallenge($challengeId);
            $this->addError('otp_code', 'OTP session expired. Please sign in again.');

            return;
        }

        $code = (string) random_int(100000, 999999);

        if (! $this->sendOtpEmail((string) ($payload['email'] ?? ''), $code)) {
            $this->addError('otp_code', 'We could not resend your OTP. Please try again.');

            return;
        }

        $payload['code_hash'] = Hash::make($code);
        $payload['attempts'] = 0;

        Cache::put(
            $this->otpCacheKey($challengeId),
            $payload,
            now()->addMinutes(self::OTP_TTL_MINUTES)
        );

        $this->otpSent = true;

        Notification::make()
            ->title('OTP resent')
            ->body('A new OTP code has been sent to your email.')
            ->success()
            ->send();

        $this->otpDigits = ['', '', '', '', '', ''];
    }

    public function sendInitialOtpIfNeeded(): void
    {
        $challengeId = $this->getChallengeIdFromSession();

        if (blank($challengeId)) {
            return;
        }

        $payload = Cache::get($this->otpCacheKey($challengeId));

        if (! is_array($payload)) {
            return;
        }

        if (($payload['otp_sent'] ?? false) === true && is_string($payload['code_hash'] ?? null) && filled($payload['code_hash'])) {
            $this->otpSent = true;

            return;
        }

        $code = (string) random_int(100000, 999999);

        if (! $this->sendOtpEmail((string) ($payload['email'] ?? ''), $code)) {
            $this->otpSent = false;
            $this->addError('otp_code', 'We could not send your verification code yet. Please click Resend OTP.');

            return;
        }

        $payload['code_hash'] = Hash::make($code);
        $payload['otp_sent'] = true;
        $payload['attempts'] = 0;

        Cache::put(
            $this->otpCacheKey($challengeId),
            $payload,
            now()->addMinutes(self::OTP_TTL_MINUTES)
        );

        $this->otpSent = true;

        Notification::make()
            ->title('Verification code sent')
            ->body('Please check your email for the OTP code.')
            ->success()
            ->send();
    }

    public function useDifferentAccount(): mixed
    {
        $this->clearChallenge((string) $this->getChallengeIdFromSession());

        return redirect()->route(self::LOGIN_ROUTE_NAME);
    }

    public function updatedOtpDigits(mixed $value, string $key): void
    {
        $index = (int) $key;

        if (! array_key_exists($index, $this->otpDigits)) {
            return;
        }

        $sanitized = preg_replace('/\D+/', '', (string) $value);

        $this->otpDigits[$index] = $sanitized === ''
            ? ''
            : substr($sanitized, -1);
    }

    private function getChallengeIdFromSession(): ?string
    {
        $challengeId = session(self::OTP_CHALLENGE_SESSION_KEY);

        if (! is_string($challengeId) || $challengeId === '') {
            return null;
        }

        return $challengeId;
    }

    private function clearChallenge(string $challengeId): void
    {
        if ($challengeId !== '') {
            Cache::forget($this->otpCacheKey($challengeId));
        }

        session()->forget(self::OTP_CHALLENGE_SESSION_KEY);
    }

    private function otpCacheKey(string $challengeId): string
    {
        return "filament-login-otp:{$challengeId}";
    }

    private function sendOtpEmail(string $email, string $otpCode): bool
    {
        try {
            Mail::raw(
                "Your AICS verification code is {$otpCode}. This code expires in 10 minutes.",
                static function ($message) use ($email): void {
                    $message
                        ->to($email)
                        ->subject('AICS Login Verification Code');
                }
            );
        } catch (\Throwable) {
            return false;
        }

        return true;
    }
}
