<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Models\forms\Form;
use App\Models\forms\Transaction;
use App\Models\messages\Message;
use App\Models\others\Concern;
use App\Models\others\Report;
use App\Models\pet\Pets;
use App\Models\transactions\Answers;
use App\Models\transactions\Found;
use App\Models\users\BlockedUsers;
use App\Models\users\UserRole;
use App\Models\users\UserStatus;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        "public_id",
        "user_role_id",
        "user_status_id",
        'firstName',
        'middleName',
        'lastName',
        'gender',
        'age',
        'houseNumber',
        'street',
        'barangay',
        'municipality',
        'contactNumber',
        'username',
        'email',
        'password',
        'user_img_path',
        'showInfo',
        "suspend_at",
        "warningCount"
    ];

    public function role()
    {
        return $this->belongsTo(UserRole::class, "user_role_id");
    }
    public function status()
    {
        return $this->belongsTo(UserStatus::class, "user_status_id");
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->public_id = (string) \Illuminate\Support\Str::uuid();
        });
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'suspend_at' => 'datetime',
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

    public function pets()
    {
        return $this->hasMany(Pets::class, "user_id");
    }

    public function forms()
    {
        return $this->hasMany(Form::class, "user_id");
    }
    public function transactions()
    {
        return $this->hasMany(Transaction::class, "user_id");
    }
    public function answers()
    {
        return $this->hasMany(Answers::class, "user_id");
    }

    public function blockedUsers()
    {
        return $this->hasMany(BlockedUsers::class, "blocked_user_id");
    }
    public function concerns()
    {
        return $this->hasMany(Concern::class, "user_id");
    }

    public function reports()
    {
        return $this->hasMany(Report::class, "reporter_id");
    }

    public function blocked_users()
    {
        return $this->hasMany(BlockedUsers::class, "user_id");
    }

    public function ai_messages()
    {
        return $this->hasMany(ChatBot::class, "user_id");
    }

    public function messages()
    {
        return $this->hasMany(Message::class, "sender_id");
    }
}
