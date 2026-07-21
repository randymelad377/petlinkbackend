<?php

namespace App\Services;

use App\Events\pets\admin\NewVerifiedPetEvent;
use App\Events\pets\users\AddPetEvent;
use App\Http\Resources\pets\PetResource;
use App\Models\pet\PetBreed;
use App\Models\pet\PetImages;
use App\Models\pet\Pets;
use App\Models\pet\PetSpecies;
use App\Models\transactions\Found;
use App\Models\transactions\Missing;
use App\Models\transactions\Questions;
use App\Models\transactions\Rehome;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

class PetService
{
    public function store($fields, $images, $user)
    {
        $insertedImages = [];

        try {
            $result = DB::transaction(function () use ($fields, $images, $user, &$insertedImages) {

                $transaction_type_id = $fields["transaction_type_id"];
                $species = PetSpecies::firstOrCreate(['species' => strtolower(trim($fields['species']))]);
                $breed = PetBreed::firstOrCreate(['pet_species_id' => $species->id, 'breed' => strtolower(trim($fields['breed']))]);
                $color = $fields["color"];
                $gender = $fields["gender"];
                $age = $fields["age"];


                $pet = $user->pets()->create(
                    [
                        "pet_status_id" => 1,
                        "transaction_type_id" => $transaction_type_id,
                        "pet_species_id" => $species->id,
                        "pet_breed_id" => $breed->id,
                        "color" => $color,
                        "gender" => $gender,
                        "age" => $age
                    ]
                );

                foreach ($fields["questions"] as $question) {
                    $pet->questions()->create(["question" => $question, "history_id" => null,]);
                }

                foreach ($images as $image) {
                    $path = $image->store('pets', 'public');
                    $pet->images()->create(["image_path" => $path]);
                    $insertedImages[] = $path;
                }

                switch ($transaction_type_id) {
                    case 1:
                        $pet->rehome()->create([
                            "description" => $fields["description"],
                            "medical_record" => $fields["medical_record"],
                            "diagnosis" => $fields["diagnosis"],
                            "vaccine_records" => $fields["vaccine_record"],
                            "current_medicines" => $fields["current_medicines"],
                        ]);
                        break;
                    case 2:
                        $pet->found()->create([
                            "description" => $fields["description"],
                            "date_found" => $fields["date_found"],
                            "found_at" => $fields["found_at"],
                        ]);
                        break;
                    case 3:
                        $pet->missing()->create([
                            "description" => $fields["description"],
                            "date_lost" => $fields["date_lost"],
                            "lost_at" => $fields["lost_at"],
                        ]);
                        break;
                }

                $pet_id = $pet->public_id;

                AddPetEvent::dispatch($user, $pet_id);

                return $pet;
            });

            return $result;
        } catch (Exception $error) {
            foreach ($insertedImages as $image) {
                Storage::disk('public')->delete($image);
            }

            throw $error;
        }
    }

    public function update($fields, $images, $pet)
    {
        $insertedImages = [];

        try {
            $result = DB::transaction(function () use ($fields, $images, $pet, &$insertedImages) {

                $species = $pet->species;
                $breed = PetBreed::firstOrCreate(['pet_species_id' => $species->id, 'breed' => strtolower(trim($fields['breed']))]);
                $color = $fields["color"];
                $gender = $fields["gender"];
                $age = $fields["age"];

                $pet->fill([
                    "pet_breed_id" => $breed->id,
                    "color" => $color,
                    "gender" => $gender,
                    "age" => $age
                ]);

                $old_images = PetImages::where("pet_id", $pet->id)->get();

                $changed = false;

                if (!empty($images)) {
                    foreach ($images as $image) {
                        $path = $image->store("pets", "public");
                        $pet->images()->create(["image_path" => $path]);
                        $insertedImages[] = $path;
                    }

                    $changed = true;
                } else {
                    $changed = $pet->isDirty();
                }

                $pet->save();

                return [
                    "data" => $pet->withoutRelations(),
                    "changed" => $changed,
                    "message" => $changed ? "Pet Updated." : "No changes detected.",
                    "old_images" => $old_images
                ];
            });


            if (!empty($images)) {
                foreach ($result["old_images"] as $image) {
                    Storage::disk('public')->delete($image->image_path);
                    $image->delete();
                }
            }

            return $result;
        } catch (Exception $error) {
            foreach ($insertedImages as $image) {
                Storage::disk('public')->delete($image);
            }

            throw $error;
        }
    }

    public function updateQuestions($questions)
    {
        $result = DB::transaction(function () use ($questions) {

            $newQuestions = [];
            foreach ($questions as $question) {
                $currentQuestion = Questions::find($question['id']);

                if ($currentQuestion) {
                    $currentQuestion->update(['question' => $question['question']]);
                    $newQuestions[] = $currentQuestion->fresh();
                }
            }

            return [
                "data" => $newQuestions,
                "changed" => true,
                "message" => "Questions updated."
            ];
        });

        return $result;
    }

    public function updateOtherPetInfo($fields, $pet)
    {
        $result = DB::transaction(function () use ($fields, $pet) {
            $transaciont_type_id = $fields["transaction_type_id"];

            switch ($transaciont_type_id) {
                case 1:
                    $other_pet_info = Rehome::where("pet_id", $pet->id)->firstOrFail();
                    $data = [
                        "description" => $fields["description"],
                        "medical_record" => $fields["medical_record"],
                        "diagnosis" => $fields["diagnosis"],
                        "vaccine_records" => $fields["vaccine_records"],
                        "current_medicines" => $fields["current_medicines"]
                    ];
                    break;
                case 2:
                    $other_pet_info = Found::where("pet_id", $pet->id)->firstOrFail();
                    $data = [
                        "description" => $fields["description"],
                        "date_found" => $fields["date_found"],
                        "found_at" => $fields["found_at"]
                    ];
                    break;
                case 3:
                    $other_pet_info = Missing::where("pet_id", $pet->id)->firstOrFail();
                    $data = [
                        "description" => $fields["description"],
                        "date_lost" => $fields["date_lost"],
                        "lost_at" => $fields["lost_at"]
                    ];
                    break;
                default:
                    throw new InvalidArgumentException('Invalid transaction type.');
            }

            $other_pet_info->fill($data);
            $changed = $other_pet_info->isDirty();
            $other_pet_info->save();

            return [
                "data" => $other_pet_info,
                "changed" => $changed,
                "message" => $changed ? "Other pet information changed." : "No changes detected"
            ];
        });

        return $result;
    }

    public function reDisplayPet($fields, $pet)
    {

        $result = DB::transaction(function () use ($fields, $pet) {
            switch ($fields["transaction_type_id"]) {
                case 1:
                    $pet->rehome()->create([
                        "description" => $fields["description"],
                        "medical_record" => $fields["medical_record"],
                        "diagnosis" => $fields["diagnosis"],
                        "vaccine_records" => $fields["vaccine_record"],
                        "current_medicines" => $fields["current_medicines"],
                    ]);
                    break;
                case 2:
                    $pet->found()->create([
                        "description" => $fields["description"],
                        "date_found" => $fields["date_found"],
                        "found_at" => $fields["found_at"],
                    ]);
                    break;
                case 3:
                    $pet->missing()->create([
                        "description" => $fields["description"],
                        "date_lost" => $fields["date_lost"],
                        "lost_at" => $fields["lost_at"],
                    ]);
                    break;
            }

            foreach ($fields["questions"] as $question) {
                $pet->questions()->create(["question" => $question, "history_id" => null,]);
            }

            $statusId = match ((int) $fields["transaction_type_id"]) {
                1 => 3,
                2 => 4,
                3 => 5,
            };

            $pet->update(["transaction_type_id" => $fields["transaction_type_id"], "pet_status_id" => $statusId]);
            NewVerifiedPetEvent::dispatch($pet, $pet->user);
            return [
                "data" => new PetResource($pet),
                "message" => "Pet has been display to community again.",
            ];
        });

        return $result;
    }
}
