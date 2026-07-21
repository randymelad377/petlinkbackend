<?php

namespace Database\Seeders\admin;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superAdminExist = User::where("user_role_id", 3)->first();

        if (!$superAdminExist) {
            $super_admin = User::create([
                "public_id" => "super_admin_public_id",
                "firstName" => "AdminFirstName",
                "middleName" => "AdminMiddleName",
                "lastName" => "AdminLastName",
                "gender" => "male",
                "age" => 10,
                "houseNumber" => "00",
                "street" => "Rizal",
                "barangay" => "Centro 9",
                "municipality" => "Tuguegarao City",
                "contactNumber" => "0912345678",
                "username" => "sadmin@377",
                "password" => Hash::make("password"),
                "email" => "fake@gmail.com",
                "user_role_id" => 3,
                "user_status_id" => 1,
                "user_img_path" => "defaults/defaultPhp.png"
            ]);
        }
    }
}
