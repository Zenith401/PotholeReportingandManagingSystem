<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

// If you're using a custom notification, un-comment this line:
 use App\Notifications\CustomVerifyEmail;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'fname',
        'lname',
        'email',
        'password',
        'role',
        'city',
        'state',
        'country',
        'zip_code',
    ];
    

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    const ROLE_PRO = 1;
    const ROLE_ADMIN = 2;
    const ROLE_GENERALUSER = 3;

    /**
     * Return the role name.
     */
    public function getRoleNameAttribute()
    {
        return match($this->role) {
            self::ROLE_PRO => 'Super Administrator',
            self::ROLE_ADMIN => 'Administrator',
            self::ROLE_GENERALUSER => 'General User',
            default => 'General User',
        };
    }

    /**
     * Override the method that sends the email verification notification.
     * This ensures it uses our custom notification (if any).
     */
    public function sendEmailVerificationNotification()
    {
        if (session('email_update')) {
            // Custom notification for email updates
            $this->notify(new \App\Notifications\CustomEmailUpdateVerification());
        } else {
            // Default notification for registration
            $this->notify(new \App\Notifications\CustomVerifyEmail());
        }
    }
    
    
}
