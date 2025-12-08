<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Support\Str;
use Laravel\Cashier\Billable;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable, Billable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'password',
        'is_admin',
        'first_name',
        'last_name',
        'company_name',
        'company_address',
        'town',
        'country',
        'vat_number'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function canAccessPanel(Panel $panel): bool
    {
        return Str::endsWith($this->email, '@emedia.hr') && $this->hasVerifiedEmail();
    }

    public function getNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name) ?: $this->email;
    }

    /**
     * Send the welcome notification with password setup instructions.
     *
     * @return void
     */
    public function sendPasswordSetupNotification()
    {
        $this->notify(new \App\Notifications\WelcomeNewUserNotification());
    }

    /**
     * Send the registration confirmation notification.
     *
     * @return void
     */
    public function sendRegistrationConfirmationNotification()
    {
        $this->notify(new \App\Notifications\RegistrationConfirmationNotification());
    }

    /**
     * Get the domains for the user.
     */
    public function domains()
    {
        return $this->hasMany(Domain::class);
    }

    /**
     * Get the licences for the user.
     */
    public function licences()
    {
        return $this->hasMany(Licence::class);
    }
}
