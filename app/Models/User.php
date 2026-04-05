<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\HasName;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser, HasName
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $table = 'user';

    protected $primaryKey = 'user_id';

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'role',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        $status = $this->normalizeAccessToken((string) ($this->status ?? ''));

        if ($status !== 'active') {
            return false;
        }

        $panelId = $this->normalizeAccessToken($panel->getId());
        $role = $this->normalizeAccessToken((string) ($this->role ?? ''));

        if ($panelId === '' || $role === '') {
            return false;
        }

        $panelRoleMap = [
            'admin' => ['admin', 'system_admin'],
            'aics_staff' => ['aics_staff'],
        ];

        $allowedRoles = $panelRoleMap[$panelId] ?? [$panelId];

        return in_array($role, $allowedRoles, true);
    }

    private function normalizeAccessToken(string $value): string
    {
        return str_replace('-', '_', strtolower(trim($value)));
    }

    public function getFilamentName(): string
    {
        $firstName = trim((string) ($this->first_name ?? ''));
        $lastName = trim((string) ($this->last_name ?? ''));
        $fullName = trim($firstName.' '.$lastName);

        if ($fullName !== '') {
            return $fullName;
        }

        $email = trim((string) ($this->email ?? ''));

        if ($email !== '') {
            return $email;
        }

        $identifier = $this->getAuthIdentifier();

        return $identifier !== null
            ? 'User #'.$identifier
            : 'User';
    }
}
