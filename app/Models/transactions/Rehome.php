<?php

namespace App\Models\transactions;

use App\Models\forms\History;
use Illuminate\Database\Eloquent\Model;

class Rehome extends Model
{
    protected $fillable = [
        "pet_id",
        "description",
        "medical_record",
        "diagnosis",
        "vaccine_records",
        "current_medicines"
    ];

    public function history()
    {
        return $this->belongsTo(History::class, "histor_id");
    }
}
