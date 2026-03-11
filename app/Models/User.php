<?php

namespace App\Models;

use App\Traits\HasPublicId;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens;

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;

    use HasPublicId;
    use HasRoles;
    use Notifiable;

    protected string $guard_name = 'web';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'public_id',
        'name',
        'first_name',
        'last_name',
        'email',
        'email_verified_at',
        'google_id',
        'signup_method',
        'password',
        'phone',
        'phone_country_code',
        'phone_local_number',
        'referral_code',
        'heard_about_source',
        'heard_about_other',
        'phone_verified_at',
        'last_seen_at',
        'profile_completed_at',
        'is_blocked',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'id',
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'last_seen_at' => 'datetime',
            'profile_completed_at' => 'datetime',
            'is_blocked' => 'boolean',
            'password' => 'hashed',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $user): void {
            $firstName = trim((string) ($user->first_name ?? ''));
            $lastName = trim((string) ($user->last_name ?? ''));

            if ($firstName !== '' || $lastName !== '') {
                $user->name = self::combineName($firstName, $lastName);
                return;
            }

            if ($user->isDirty('name')) {
                [$derivedFirstName, $derivedLastName] = self::splitName($user->name);
                $user->first_name = $derivedFirstName;
                $user->last_name = $derivedLastName;
            }
        });
    }

    public function providerProfile(): HasOne
    {
        return $this->hasOne(ProviderProfile::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function refreshTokens(): HasMany
    {
        return $this->hasMany(RefreshToken::class);
    }

    public function identityVerification(): HasOne
    {
        return $this->hasOne(UserIdentityVerification::class);
    }

    /**
     * @return array{0:string,1:?string}
     */
    public static function splitName(?string $fullName): array
    {
        $normalized = trim((string) $fullName);
        if ($normalized === '') {
            return ['', null];
        }

        $parts = preg_split('/\s+/', $normalized) ?: [];
        $firstName = array_shift($parts) ?: $normalized;
        $lastName = trim(implode(' ', $parts));

        return [$firstName, $lastName !== '' ? $lastName : null];
    }

    public static function combineName(?string $firstName, ?string $lastName): string
    {
        return trim(implode(' ', array_filter([
            trim((string) $firstName),
            trim((string) $lastName),
        ])));
    }
}
