<?php

namespace App\Models\transactions;

use App\Models\forms\History;
use Illuminate\Database\Eloquent\Model;

class Found extends Model
{
    protected $fillable = [
        "pet_id",
        "description",
        "date_found",
        "found_at"
    ];

    public function history()
    {
        return $this->belongsTo(History::class, "histor_id");
    }
}
