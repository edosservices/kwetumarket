<?php

namespace App\Models;

use App\Enums\AccountArea;
use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'phone', 'locale', 'currency'])]
#[Hidden(['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, Notifiable, TwoFactorAuthenticatable;

    protected string $guard_name = 'web';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'suspended_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function socialAccounts(): HasMany
    {
        return $this->hasMany(SocialAccount::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function ownedVendor(): HasMany
    {
        return $this->hasMany(Vendor::class);
    }

    public function vendorId(): ?int
    {
        return $this->vendor_id === null ? null : (int) $this->vendor_id;
    }

    public function area(): AccountArea
    {
        return AccountArea::for($this);
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole(UserRole::SuperAdmin->value);
    }

    public function isPlatformStaff(): bool
    {
        return $this->hasAnyRole(array_map(
            fn (UserRole $role) => $role->value,
            UserRole::platform(),
        ));
    }

    public function isVendorSide(): bool
    {
        return $this->hasAnyRole(array_map(
            fn (UserRole $role) => $role->value,
            UserRole::vendorSide(),
        ));
    }

    public function isSuspended(): bool
    {
        return $this->suspended_at !== null;
    }

    public function courierProfile(): HasOne
    {
        return $this->hasOne(CourierProfile::class);
    }

    public function canModerateReviews(): bool
    {
        return $this->isSuperAdmin() || ($this->can('products.approve') && $this->can('support.view'));
    }
}
