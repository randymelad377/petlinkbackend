<?php

namespace App\Models\forms;

use App\Models\pet\Pets;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        "pet_id",
        "user_id",
        "form_id",
        "transaction_status_id",
        "ownerMarkAsDone",
        "requesterMarkAsDone",
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->public_id = (string) \Illuminate\Support\Str::uuid();
        });
    }

    public function pet()
    {
        return $this->belongsTo(Pets::class, "pet_id");
    }
    public function user()
    {
        return $this->belongsTo(User::class, "user_id");
    }
    public function form()
    {
        return $this->belongsTo(Form::class, "form_id");
    }
    public function transaction()
    {
        return $this->belongsTo(Transaction::class, "transaction_id");
    }
    public function transaction_status()
    {
        return $this->belongsTo(TransactionStatus::class, "transaction_status_id");
    }
}
