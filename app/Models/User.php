<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Jobs\HandleUserUpdate;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected static function boot()
    {
        parent::boot();

        static::updated(function ($user) {
            self::queueUserUpdate($user);
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'firstName',
        'lastName',
        'email',
        'timezone',
        'password',
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
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected static function queueUserUpdate(User $user)
    {
        $updatedFields = $user->getDirty();

        $data = ['email' => $user->email];

        foreach ($updatedFields as $key => $value) {
            $data[$key] = $value;
        }

        // Store in cache until the batch has 1000 updates
        Cache::add('user_updates', $data);

        // Dispatch job if batch has 1000 updates
        if (Cache::get('user_updates')->count() >= 1000) {
            $updates = Cache::pull('user_updates');
            HandleUserUpdate::dispatch($updates);
        }
    }
}
