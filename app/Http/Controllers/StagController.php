<?php

namespace App\Http\Controllers;

use App\Classes\ActivityLogClass;
use App\Exports\StagSummaryExport;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Responses\ApiErrorResponse;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\Stag\ExportStagSummaryRequest;

class StagController extends Controller
{
    public function exportStagSummary(ExportStagSummaryRequest $request)
    {
        try {

            $chapter = $request->input('chapter');

            return Excel::download(new StagSummaryExport($chapter), 'chapter_summary.csv', \Maatwebsite\Excel\Excel::CSV, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="chapter_summary.csv"',
            ]);

        } catch (\Exception $e) {
            Log::error($e);

            ActivityLogClass::create('Export Stag Summary Failed', null, [
                'user_id' => auth()->user()->id ?? null,
                'role' => auth()->user()->role->value ?? null,
                'status' => 'error',
            ]);

            return new ApiErrorResponse(
                'An error occured while exporting data.',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
