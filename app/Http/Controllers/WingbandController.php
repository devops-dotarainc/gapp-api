<?php

namespace App\Http\Controllers;

use App\Http\Requests\Wingband\ImportWingbandRequest;
use App\Http\Responses\ApiErrorResponse;
use App\Http\Responses\ApiSuccessResponse;
use App\Imports\WingbandImport;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;

class WingbandController extends Controller
{
    public function importWingband(ImportWingbandRequest $request)
    {
        if (! $request->hasFile('excel_file')) {
            return new ApiErrorResponse(
                'No file uploaded',
                Response::HTTP_BAD_REQUEST
            );
        }

        $file = $request->file('excel_file');

        try {

            Excel::import(new WingbandImport, $file);

            return new ApiSuccessResponse(
                ['message' => 'Wingbands imported successfully please check the excel data uploaded to the system'],
                Response::HTTP_CREATED
            );

        } catch (\Exception $e) {

            Log::error($e);

            $errMsg = json_decode($e->getMessage());

            if (! is_null($errMsg)) {
                if (isset($errMsg->unsaved_data) && $errMsg->unsaved_data === true) {
                    unset($errMsg->unsaved_data);

                    $values = array_values((array) $errMsg);
                    $valueString = implode(', ', $values);

                    return new ApiErrorResponse(
                        'Failed to import excel data, please check the following row number for blank data! Rows: '.$valueString,
                        Response::HTTP_INTERNAL_SERVER_ERROR
                    );
                }
                if (isset($errMsg->duplicate_data) && $errMsg->duplicate_data === true) {
                    unset($errMsg->duplicate_data);

                    $values = array_values((array) $errMsg);
                    $valueString = implode(', ', $values);

                    return new ApiErrorResponse(
                        'Failed to import excel data, please check the following row number for duplicate wingband data! Rows: '.$valueString,
                        Response::HTTP_INTERNAL_SERVER_ERROR
                    );
                }
            }

            return new ApiErrorResponse(
                'An error occured while importing wingband data.',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
