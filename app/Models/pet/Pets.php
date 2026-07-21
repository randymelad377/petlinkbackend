<?php

namespace App\Models\pet;

use App\Models\forms\Form;
use App\Models\forms\History;
use App\Models\forms\Transaction;
use App\Models\transactions\Answers;
use App\Models\transactions\Found;
use App\Models\transactions\Missing;
use App\Models\transactions\Questions;
use App\Models\transactions\Rehome;
use App\Models\transactions\TransactionType;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Pets extends Model
{

    public function getRouteKeyName()
    {
        return 'public_id';
    }

    protected $fillable = [
        "transaction_type_id",
        "user_id",
        "pet_status_id",
        "pet_species_id",
        "pet_breed_id",
        "color",
        "gender",
        "age"
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->public_id = (string) \Illuminate\Support\Str::uuid();
        });
    }


    public function user()
    {
        return $this->belongsTo(User::class, "user_id");
    }

    public function species()
    {
        return $this->belongsTo(PetSpecies::class, "pet_species_id");
    }

    public function breed()
    {
        return $this->belongsTo(PetBreed::class, "pet_breed_id");
    }

    public function status()
    {
        return $this->belongsTo(PetStatus::class, "pet_status_id");
    }

    public function images()
    {
        return $this->hasMany(PetImages::class, "pet_id");
    }

    public function questions()
    {
        return $this->hasMany(Questions::class, "pet_id");
    }

    public function rehome()
    {
        return $this->hasOne(Rehome::class, "pet_id");
    }
    public function found()
    {
        return $this->hasOne(Found::class, "pet_id");
    }
    public function missing()
    {
        return $this->hasOne(Missing::class, "pet_id");
    }

    public function transaction_type()
    {
        return $this->belongsTo(TransactionType::class, "transaction_type_id");
    }

    public function forms()
    {
        return $this->hasMany(Form::class, "pet_id");
    }
    public function transaction()
    {
        return $this->hasOne(Transaction::class, "pet_id");
    }
    public function answers()
    {
        return $this->hasMany(Answers::class, "pet_id");
    }
    public function histories()
    {
        return $this->hasMany(History::class, "pet_id");
    }
    public function previousOwners()
    {
        return $this->hasMany(PetPreviousOwner::class, "pet_id");
    }
}
