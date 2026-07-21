<?php

namespace App\Models\pet;

use App\Models\forms\TransactionStatus;
use Illuminate\Database\Eloquent\Model;

class PetPreviousOwner extends Model
{
    protected $fillable = [
        "pet_id",
        "user_id",
        "current_user_id",
        "ownership_order",
        "transaction_type_id",
        "history_id"
    ];

    public function pet()
    {
        return $this->belongsTo(Pets::class, "pet_id");
    }
    public function transaction_status()
    {
        return $this->belongsTo(TransactionStatus::class, "transaction_status_id");
    }
}
