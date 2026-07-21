<?php

namespace App\Models\pet;

use Illuminate\Database\Eloquent\Model;

class PetBreed extends Model
{
    protected $fillable = [
        "breed",
        "pet_species_id"
    ];

    public function species()
    {
        return $this->belongsTo(PetSpecies::class, "pet_species_id");
    }

    public function pets()
    {
        return $this->hasMany(Pets::class, "pet_breed_id");
    }
}
