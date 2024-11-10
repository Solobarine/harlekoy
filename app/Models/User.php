<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Jobs\HandleUserUpdate;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected static function booted()
    {
        parent::boot();

        static::updated(function ($user) {
            $user->queueUserUpdate($user);
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
        Cache::put('user_updates', $data);
        Log::info("Saved User Update to Cache: {$user->id}");
        // Dispatch job if batch has 1000 updates
        if (count(Cache::get('user_updates')) >= 1000) {
            $updates = Cache::pull('user_updates');
            HandleUserUpdate::dispatch($updates);
        }
    }
}
