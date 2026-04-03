<?php

namespace App\Filament\Pages\Auth;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Actions\Action;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Facades\Filament;
use Filament\Models\Contracts\FilamentUser;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\SessionGuard;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use SensitiveParameter;

class Login extends BaseLogin
{
    private const OTP_TTL_MINUTES = 10;

    private const OTP_CHALLENGE_SESSION_KEY = 'filament_login_otp_challenge_id';

    public function getHeading(): string
    {
        return 'AICS Login';
    }

    public function getSubheading(): ?string
    {
        return 'Sign in with your active account to continue.';
    }

    public function authenticate(): ?LoginResponse
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return null;
        }

        $data = $this->form->getState();

        /** @var SessionGuard $authGuard */
        $authGuard = Filament::auth();
        $authProvider = $authGuard->getProvider(); /** @phpstan-ignore-line */
        $credentials = $this->getCredentialsFromFormData($data);

        $user = $authProvider->retrieveByCredentials($credentials);

        if ((! $user) || (! $authProvider->validateCredentials($user, $credentials))) {
            $this->fireFailedEvent($authGuard, $user, $credentials);
            $this->throwFailureValidationException();
        }

        if ($user instanceof FilamentUser && (! $user->canAccessPanel(Filament::getCurrentOrDefaultPanel()))) {
            $this->throwFailureValidationException();
        }

        $challengeId = $this->startOtpChallenge($user, (bool) ($data['remember'] ?? false));

        if (blank($challengeId)) {
            throw ValidationException::withMessages([
                'data.email' => 'We could not start verification. Please try again.',
            ]);
        }

        session()->put(self::OTP_CHALLENGE_SESSION_KEY, $challengeId);

        $this->redirectRoute('filament.auth.otp', navigate: true);

        Notification::make()
            ->title('Verification started')
            ->body('Preparing your verification code. Continue on the OTP page.')
            ->success()
            ->send();

        return null;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function getCredentialsFromFormData(array $data): array
    {
        return [
            'email' => strtolower((string) ($data['email'] ?? '')),
            'password' => (string) ($data['password'] ?? ''),
        ];
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getRememberFormComponent(),
            ]);
    }

    /**
     * @return array<Action>
     */
    protected function getFormActions(): array
    {
        return [
            $this->getAuthenticateFormAction(),
        ];
    }

    protected function getAuthenticateFormAction(): Action
    {
        return Action::make('authenticate')
            ->label('Sign in')
            ->submit('authenticate');
    }

    protected function throwFailureValidationException(): never
    {
        throw ValidationException::withMessages([
            'data.email' => 'These credentials do not match an active account.',
        ]);
    }

    protected function startOtpChallenge(Authenticatable $user, bool $remember): ?string
    {
        $email = (string) ($user->email ?? '');

        if ($email === '') {
            return null;
        }

        $challengeId = bin2hex(random_bytes(16));

        Cache::put(
            $this->otpCacheKey($challengeId),
            [
                'user_id' => $user->getAuthIdentifier(),
                'email' => $email,
                'code_hash' => null,
                'otp_sent' => false,
                'attempts' => 0,
                'remember' => $remember,
            ],
            now()->addMinutes(self::OTP_TTL_MINUTES)
        );

        return $challengeId;
    }

    protected function otpCacheKey(string $challengeId): string
    {
        return "filament-login-otp:{$challengeId}";
    }

    /**
     * @param  array<string, mixed>  $credentials
     */
    protected function fireFailedEvent(Guard $guard, ?Authenticatable $user, #[SensitiveParameter] array $credentials): void
    {
        event(app(Failed::class, [
            'guard' => property_exists($guard, 'name') ? $guard->name : '',
            'user' => $user,
            'credentials' => $credentials,
        ]));
    }
}
