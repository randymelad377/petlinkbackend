<?php

namespace App\Models\pet;

use Illuminate\Database\Eloquent\Model;

class PetImages extends Model
{
    protected $fillable = [
        "pet_id",
        "image_path"
    ];
}
