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
}
