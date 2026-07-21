<?php

namespace App\Services;

use App\Events\users\NewUserEvent;
use App\Helpers\ApiResponse;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UserService
{
    public function register($fields, $image)
    {
        $insertedImage = [];

        try {
            $user = DB::transaction(function () use ($fields, $image, &$insertedImage) {
                if (!empty($image)) {
                    $path = $image->store('users', 'public');
                    $fields["user_img_path"] = $path;
                    $insertedImage[] = $path;
                } else {
                    $fields["user_img_path"] = "defaults/defaultPhp.png";
                }
                $user = User::create($fields);
                NewUserEvent::dispatch($user);
                return $user;
            });

            return $user;
        } catch (Exception $error) {
            foreach ($insertedImage as $path) {
                Storage::disk('public')->delete($path);
            }

            throw $error;
        }
    }

    public function update($fields, $user, $image)
    {
        $insertedImg = null;

        try {
            $user = DB::transaction(function () use ($fields, $user, $image, &$insertedImg) {

                $oldImg = null;

                $user->fill($fields);

                if ($image) {
                    $oldImg = $user->user_img_path;
                    $path = $image->store("users", "public");
                    $user->user_img_path = $path;
                    $insertedImg = $path;
                }

                $changed = $user->isDirty();
                $user->save();

                return [
                    'user' => $user,
                    'changed' => $changed,
                    'canUpdate' => false,
                    'oldImg' => $oldImg
                ];
            });

            if (!empty($user["oldImg"])) {
                Storage::disk("public")->delete($user["oldImg"]);
            }

            return $user;
        } catch (Exception $error) {

            if ($insertedImg) {
                Storage::disk('public')->delete($insertedImg);
            }

            throw $error;
        }
    }
}
