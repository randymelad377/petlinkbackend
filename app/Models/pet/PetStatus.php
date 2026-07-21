<?php

namespace App\Models\pet;

use Illuminate\Database\Eloquent\Model;

class PetStatus extends Model
{
    protected $fillable = [
        "status"
    ];

    protected $table = 'pet_statuses';

    public function pets()
    {
        return $this->hasMany(Pets::class, "pet_status_id");
    }
}
