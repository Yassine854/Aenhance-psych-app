<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReportRequest;
use App\Models\Report;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\JsonResponse;
use App\Services\ActivityLogger;

class ReportController extends Controller
{
    /**
     * Store a new report.
     */
    public function store(StoreReportRequest $request): JsonResponse
    {
        $data = $request->only([
            'reporter_id',
            'reporter_type',
            'reported_id',
            'reported_type',
            'reason',
        ]);

        if ($request->hasFile('proof_image')) {
            $file = $request->file('proof_image');

            $result = Cloudinary::uploadFile($file->getRealPath(), [
                'folder' => 'reports',
                'resource_type' => 'image',
            ]);

            if (is_object($result) && method_exists($result, 'getSecurePath')) {
                $data['proof_image'] = $result->getSecurePath();
            } elseif (is_array($result) && isset($result['secure_url'])) {
                $data['proof_image'] = $result['secure_url'];
            } else {
                $response = is_object($result) && method_exists($result, 'getResponse') ? $result->getResponse() : null;
                $data['proof_image'] = $response['secure_url'] ?? null;
            }
        }

        $report = Report::create($data);

        ActivityLogger::log(auth()->id() ?? $data['reporter_id'] ?? null, auth()->user()?->role ?? $data['reporter_type'] ?? null, 'created_report', 'Report', $report->id, $data['reason'] ?? null);

        return response()->json(['data' => $report], 201);
    }
}
