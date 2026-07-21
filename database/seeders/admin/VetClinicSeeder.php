<?php

namespace Database\Seeders\admin;

use App\Models\VetClinic;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VetClinicSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clinicsAttributes = [
            ["latitude" => 17.62578022033312, "longitude" => 121.71988218780206, "clinic_name" => "Bark Avenue Clinic"],
            ["latitude" => 17.626630125117593, "longitude" => 121.73059671677103, "clinic_name" => "Mendoza Animal Clinic"],
            ["latitude" => 17.613100981777123, "longitude" => 121.72732471101797, "clinic_name" => "Paw Print Veterinary Clinic and Accesories"],
            ["latitude" => 17.622274550729678, "longitude" => 121.72451749507596, "clinic_name" => "Wizard of Paws Veterinary Clinic and Grooming Center"],
            ["latitude" => 17.614779182236816, "longitude" => 121.72902669997353, "clinic_name" => "ICT VET Veterinary Clinic"],
            ["latitude" => 17.626935193925913, "longitude" => 121.73513342083812, "clinic_name" => "Sniffs and Licks Veterinary Clinic - Tuguegarao City Main"],
            ["latitude" => 17.61342908233243, "longitude" =>  121.71753002758471, "clinic_name" => "RA Animal Wellness Center"],
            ["latitude" => 17.630696050261747, "longitude" =>  121.74033146326202, "clinic_name" => "Urban Fur Veterinary Clinic"],
            ["latitude" => 17.635522132199235, "longitude" =>  121.73715572792956, "clinic_name" => "Waggie Tail Veterinary Clinic"],
            ["latitude" => 17.619162009159073, "longitude" =>  121.72840099809409, "clinic_name" => "Animal Option's Veterinary Clinic"],
            ["latitude" => 17.65228971723591, "longitude" =>  121.74651127255767, "clinic_name" => "DOC JESM VETERINARY CLINIC"],
        ];

        foreach ($clinicsAttributes as $clinic) {
            VetClinic::create(["latitude" => $clinic["latitude"], "longitude" => $clinic["longitude"], "clinic_name" => $clinic["clinic_name"]]);
        }
    }
}
