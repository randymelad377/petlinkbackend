<?php

namespace App\Http\Controllers\others;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\others\AllReportResource;
use App\Models\others\Report;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user && $user->user_role_id === 1) {
            ApiResponse::error("Forbidden.", 403, null);
        }

        $reports = Report::with('user', "reporter")->get();

        return ApiResponse::success(AllReportResource::collection($reports), "All Concerns", 200);
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

        $reporter = $request->user();

        $fields = $request->validate([
            'message' => 'nullable|string|min:3|required_without:image',
            'image'   => 'nullable|image|mimes:jpg,jpeg,png|max:2048|required_without:message',
            "user_id" => "required|string"
        ]);


        $lastReport = $reporter->reports()
            ->with("user")
            ->where("user_id", $fields["user_id"])
            ->orderBy("id", "desc")
            ->first();

        if ($lastReport && $lastReport->created_at->diffInDays(now()) < 1) {
            return ApiResponse::error("Please wait after 1 days before reporting this user again.", 403, null);
        }

        $result = DB::transaction(function () use ($fields, $reporter, $request) {
            $image_path = null;

            if ($request->hasFile('image')) {
                $image_path = $request->file('image')->store('reports', 'public');
            }

            $reportedId = User::where("public_id", $fields["user_id"])->first()->id;
            $report = $reporter->reports()->create(["user_id" => $reportedId, "message" => $fields["message"], "image_path" => $image_path, "isRead" => false]);

            return $report;
        });

        return ApiResponse::success($result, "Report sent to admin group.", 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

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
        $report = Report::where("id", $id)->first();
        $isRead = $report->isRead;

        $report->update(["isRead" => !$isRead]);

        return ApiResponse::success(AllReportResource::collection(Report::with("user", "reporter")->get()), $isRead ? "Mark as read" : "Mark as unread", 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $report = Report::where('id', $id)->first();

        if (!$report) {
            return ApiResponse::error("Report not found", 404, null);
        }
        $report->delete();
        return ApiResponse::success(AllReportResource::collection(Report::with("user", "reporter")->get()), "Report Deleted", 200);
    }
}
