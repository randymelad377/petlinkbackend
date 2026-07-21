<?php

namespace App\Models\transactions;

use App\Models\forms\History;
use Illuminate\Database\Eloquent\Model;

class Missing extends Model
{
    protected $fillable = [
        "pet_id",
        "description",
        "date_lost",
        "lost_at"
    ];

    public function history()
    {
        return $this->belongsTo(History::class, "histor_id");
    }
}
