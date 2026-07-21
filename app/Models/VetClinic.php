<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VetClinic extends Model
{
    protected $fillable = [
        "latitude",
        "longitude",
        "clinic_name",
        "isOpen"
    ];
}
