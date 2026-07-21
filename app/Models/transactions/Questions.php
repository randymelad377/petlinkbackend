<?php

namespace App\Models\transactions;

use App\Models\forms\History;
use App\Models\pet\Pets;
use Illuminate\Database\Eloquent\Model;

class Questions extends Model
{
    protected $fillable = [
        "history_id",
        "pet_id",
        "question"
    ];

    public function answers()
    {
        return $this->hasMany(Answers::class, "question_id");
    }
    public function history()
    {
        return $this->hasMany(History::class, "history_id");
    }
}
