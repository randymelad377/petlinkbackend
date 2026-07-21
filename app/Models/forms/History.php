<?php

namespace App\Models\forms;

use App\Models\pet\Pets;
use App\Models\transactions\Found;
use App\Models\transactions\Missing;
use App\Models\transactions\Questions;
use App\Models\transactions\Rehome;
use App\Models\transactions\TransactionType;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class History extends Model
{
    protected $fillable = [
        "transaction_type_id",
        "pet_poster_id",
        "pet_interested_id",
        "pet_id",
        "transaction_started",
        "ownerShipOrder",
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->public_id = (string) \Illuminate\Support\Str::uuid();
        });
    }
    public function transaction_type()
    {
        return $this->belongsTo(TransactionType::class, "transaction_type_id");
    }
    public function pet_poster()
    {
        return $this->belongsTo(User::class, "pet_poster_id");
    }
    public function pet_interested()
    {
        return $this->belongsTo(User::class, "pet_interested_id");
    }
    public function pet()
    {
        return $this->belongsTo(Pets::class, "pet_id");
    }
    public function questions()
    {
        return $this->hasMany(Questions::class, "history_id");
    }

    public function rehome()
    {
        return $this->hasOne(Rehome::class, "history_id");
    }
    public function found()
    {
        return $this->hasOne(Found::class, "history_id");
    }
    public function missing()
    {
        return $this->hasOne(Missing::class, "history_id");
    }
}
