<?php

namespace App\Http\Controllers;

use App\Helpers\Cryptor;
use App\Models\Wingband;
use App\Imports\WingbandImport;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Responses\ApiErrorResponse;
use App\Http\Responses\ApiSuccessResponse;
use App\Http\Requests\Wingband\UpdateRequest;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\Wingband\ImportWingbandRequest;

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

    public function update(UpdateRequest $request, $id)
    {
        try {

            $id = Cryptor::decrypt($id);

            if (! $id) {
                return new ApiErrorResponse(
                    'Invalid Wingband ID.',
                    Response::HTTP_NOT_FOUND
                );
            }

            $wingband = Wingband::find($id);        

            if(!isset($wingband)) {
                return new ApiErrorResponse(
                    'Wingband Not Found.',
                    Response::HTTP_NOT_FOUND
                );
            }

            Gate::authorize('update', $wingband);

            $data = $request->only(
                'stag_registry',
                'breeder_name',
                'farm_name',
                'farm_address',
                'province',
                'wingband_number',
                'feather_color',
                'leg_color',
                'comb_shape',
                'nose_markings',
                'feet_markings',
                'status'
            );

            $wingband->fill($data);

            if($wingband->isClean()) {
                return new ApiErrorResponse(
                    'No changes made.',
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
            }

            $wingband->updated_by = auth()->user()->id;

            $wingband->save();

            return new ApiSuccessResponse(
                null,
                ['message' => 'Wingband updated successfully.'],
                Response::HTTP_CREATED
            );

        } catch (\Exception $e) {
            \Log::error($e);

            return new ApiErrorResponse(
                'An error occured while updating wingband data.',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    public function delete($id)
    {
        try {

            $id = Cryptor::decrypt($id);

            if (! $id) {
                return new ApiErrorResponse(
                    'Invalid Wingband ID.',
                    Response::HTTP_NOT_FOUND
                );
            }

            $wingband = Wingband::find($id);

            if(!isset($wingband)) {
                return new ApiErrorResponse(
                    'Wingband Not Found.',
                    Response::HTTP_NOT_FOUND
                );
            }

            Gate::authorize('delete', $wingband);

            $wingband->deleted_by = auth()->user()->id;

            $wingband->delete();

            return new ApiSuccessResponse(
                null,
                ['message' => 'Wingband deleted successfully.'],
                Response::HTTP_CREATED
            );

        } catch (\Exception $e) {
            \Log::error($e);

            return new ApiErrorResponse(
                'An error occured while updating wingband data.',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
