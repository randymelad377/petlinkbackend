<?php

namespace App\Models\transactions;

use Illuminate\Database\Eloquent\Model;

class Answers extends Model
{
    protected $fillable = [
        "question_id",
        "user_id",
        "pet_id",
        "answer"
    ];

    public function question()
    {
        return $this->belongsTo(Questions::class, "question_id");
    }
}
