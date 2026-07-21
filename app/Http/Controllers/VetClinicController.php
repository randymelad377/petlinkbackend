<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Resources\VetClinicResource;
use App\Models\VetClinic;
use Illuminate\Http\Request;

class VetClinicController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $clinics = VetClinic::get();

        return ApiResponse::success(VetClinicResource::collection($clinics), "All clinics", 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $fields = $request->validate([
            "clinic_name" => "nullable|string|max:255",
            "latitude" => "nullable|numeric|between:-90,90",
            "longitude" => "nullable|numeric|between:-180,180"
        ]);

        $isExist = VetClinic::where("latitude", $fields["latitude"])->where("longitude", $fields["longitude"])->first();

        if ($isExist) {
            return ApiResponse::error("Clinic exist", 403, null);
        }

        $clinic = VetClinic::create([
            "clinic_name" => $fields["clinic_name"],
            "latitude" => $fields["latitude"],
            "longitude" => $fields["longitude"],
        ]);

        if (!$clinic) {
            return ApiResponse::error("Something went wrong.", 500, null);
        }

        return ApiResponse::success(
            VetClinicResource::collection(VetClinic::get()),
            "Added.",
            200
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id) {}

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $clinic = VetClinic::find($id);

        if (!$clinic) {
            return ApiResponse::error("Clinic not found.", 404);
        }

        $fields = $request->validate([
            "clinic_name" => "nullable|string|max:255",
            "latitude" => "nullable|numeric|between:-90,90",
            "longitude" => "nullable|numeric|between:-180,180",
            "isOpen" => "nullable|bool"
        ]);

        $clinic->fill(array_filter($fields, fn($value) => $value !== null));

        if (!$clinic->isDirty()) {
            return ApiResponse::error("No changes detected", 403, null);
        }

        $clinic->save();

        return ApiResponse::success(
            VetClinicResource::collection(VetClinic::get()),
            "Updated.",
            200
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $clinic = VetClinic::where("id", $id)->first();

        if (!$clinic) {
            return ApiResponse::success("Clinic not found", 404, null);
        }

        $clinic->delete();

        return ApiResponse::success(
            VetClinicResource::collection(VetClinic::get()),
            "Deleted.",
            200
        );
    }
}
