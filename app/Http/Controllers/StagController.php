<?php

namespace App\Http\Controllers;

use App\Exports\StagSummaryExport;
use App\Http\Requests\Stag\ExportStagSummaryRequest;
use App\Http\Responses\ApiErrorResponse;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;

class StagController extends Controller
{
    public function exportStagSummary(ExportStagSummaryRequest $request)
    {
        try {

            $chapter = $request->input('chapter');

            return Excel::download(new StagSummaryExport($chapter), 'chapter_summary.csv', \Maatwebsite\Excel\Excel::CSV, [
                'Content-Type' => 'text/csv',
            ]);

        } catch (\Exception $e) {
            Log::error($e);

            return new ApiErrorResponse(
                'An error occured while exporting data.',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
