<?php

namespace App\Models\pet;

use Illuminate\Database\Eloquent\Model;

class PetSpecies extends Model
{
    protected $fillable = [
        "species"
    ];

    public function breeds()
    {
        return $this->hasMany(PetBreed::class, "pet_species_id");
    }

    public function pets()
    {
        return $this->hasMany(Pets::class, "pet_species_id");
    }
}
