<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Enums\Season;
use App\Helpers\Cryptor;
use App\Http\Requests\Wingband\ImportWingbandRequest;
use App\Http\Requests\Wingband\IndexRequest;
use App\Http\Requests\Wingband\StoreWingbandRequest;
use App\Http\Requests\Wingband\UpdateRequest;
use App\Http\Responses\ApiErrorResponse;
use App\Http\Responses\ApiSuccessResponse;
use App\Imports\WingbandImport;
use App\Models\Breeder;
use App\Models\Chapter;
use App\Models\Farm;
use App\Models\Season as ModelsSeason;
use App\Models\Stag;
use App\Models\Wingband;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;

class WingbandController extends Controller
{
    public function index(IndexRequest $request)
    {
        $limit = $request->limit ?? 50;

        $sort = $request->sort ?? 'id';

        $order = $request->order ?? 'asc';

        $wingbands = Wingband::with('user:id,username')
            ->select(
                'id',
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
                'wingband_date',
                'season',
                'created_by',
            );

        if (auth()->user()->role == Role::ENCODER) {
            $wingbands->where('created_by', auth()->user()->id);
        }

        if (isset($request->season)) {
            $wingbands->where('season', $request->season);
        }

        if (isset($request->stag_registry)) {
            $wingbands->where('stag_registry', $request->stag_registry);
        }

        if (isset($request->breeder_name)) {
            $wingbands->where('breeder_name', $request->breeder_name);
        }

        if (isset($request->wingband_number)) {
            $wingbands->where('wingband_number', $request->wingband_number);
        }

        if (isset($request->updated_by)) {
            $wingbands->where('created_by', Cryptor::decrypt($request->updated_by));
        }

        if (isset($request->wingband_year)) {
            $wingbands->whereYear('wingband_date', $request->wingband_year);
        }

        if (isset($request->search)) {
            $search = $request->search;

            $wingbands->where('stag_registry', 'LIKE', "%$search%")
                ->orWhere('breeder_name', 'LIKE', "%$search%")
                ->orWhere('farm_name', 'LIKE', "%$search%")
                ->orWhere('farm_address', 'LIKE', "%$search%")
                ->orWhere('province', 'LIKE', "%$search%")
                ->orWhere('wingband_number', 'LIKE', "%$search%")
                ->orWhere('feather_color', 'LIKE', "%$search%")
                ->orWhere('leg_color', 'LIKE', "%$search%")
                ->orWhere('comb_shape', 'LIKE', "%$search%")
                ->orWhere('nose_markings', 'LIKE', "%$search%")
                ->orWhere('feet_markings', 'LIKE', "%$search%")
                ->orWhere('wingband_date', 'LIKE', "%$search%");
        }

        if ($wingbands->doesntExist()) {
            return new ApiErrorResponse(
                'No wingbands found.',
                Response::HTTP_NOT_FOUND
            );
        }

        $data = $wingbands->orderBy($sort, $order)->paginate($limit);

        $data->getCollection()->transform(function ($wingband) {
            $wingband->_id = Cryptor::encrypt($wingband->id);
            $wingband->created_by = $wingband->user->username;
            $wingband->season_name = $wingband->season->label();

            unset($wingband->user, $wingband->id, $wingband->season);

            return $wingband;
        });

        return new ApiSuccessResponse(
            $data,
            ['message' => 'Wingbands retrieved successfully!'],
            Response::HTTP_OK
        );
    }

    public function storeWingband(StoreWingbandRequest $requests)
    {
        try {
            foreach ($requests->wingband_data as $request) {

                DB::beginTransaction();

                $date = Carbon::parse($requests['wingband_date']);

                $seasonRanges = [
                    ['start' => '01-02', 'end' => '01-30', 'season' => Season::EARLY_BIRD],
                    ['start' => '03-01', 'end' => '03-30', 'season' => Season::LOCAL],
                    ['start' => '04-01', 'end' => '04-30', 'season' => Season::NATIONAL],
                    ['start' => '06-01', 'end' => '06-30', 'season' => Season::LATE_BORN],
                ];

                $seasons = null;
                foreach ($seasonRanges as $range) {
                    if ($date->format('m-d') >= $range['start'] && $date->format('m-d') <= $range['end']) {
                        $seasons = $range['season'];
                        break;
                    }
                }

                if (! $seasons) {
                    return new ApiErrorResponse(
                        'Invalid date, cannot set appropriate season.',
                        Response::HTTP_BAD_REQUEST
                    );
                }

                $checkWingband = Wingband::where('wingband_number', $request['wingband_number'])
                    ->orderBy('created_at', 'desc')
                    ->first();

                if (! is_null($checkWingband)) {
                    $wingbandDate = Carbon::parse($checkWingband->wingband_date);
                    if ($wingbandDate->year == Carbon::now()->year) {
                        return new ApiErrorResponse(
                            'Duplicate wingband number '.$checkWingband->wingband_number,
                            Response::HTTP_BAD_REQUEST
                        );
                    }
                }

                Wingband::create([
                    'stag_registry' => $request['stag_number'],
                    'breeder_name' => ucwords($request['breeders']),
                    'farm_name' => ucwords($request['farm_name']),
                    'farm_address' => ucwords($request['farm_address']),
                    'province' => $request['province'],
                    'wingband_number' => $request['wingband_number'],
                    'feather_color' => $request['feather_color'],
                    'leg_color' => $request['leg_color'],
                    'comb_shape' => $request['comb_shape'],
                    'nose_markings' => $request['nose_markings'],
                    'feet_markings' => $request['feet_markings'],
                    'season' => $seasons,
                    'wingband_date' => $date,
                    'created_by' => auth()->user()->id,
                ]);

                $checkStag = Stag::where('stag_registry', $request['stag_number'])->first();

                if (! $checkStag) {
                    $stag = new Stag;
                    $stag->stag_registry = $request['stag_number'];
                    $stag->farm_name = ucwords($request['farm_name']);
                    $stag->farm_address = ucwords($request['farm_address']);
                    $stag->breeder_name = ucwords($request['breeders']);
                    $stag->chapter = ucfirst($request['chapter']);
                    $stag->banded_cockerels = 1;
                    $stag->save();
                } else {
                    $checkStag->banded_cockerels += 1;
                    $checkStag->save();
                }

                $checkBreeder = Breeder::where('name', ucwords($request['breeders']))->first();

                if (! $checkBreeder) {
                    $breeder = new Breeder;
                    $breeder->name = ucwords($request['breeders']);
                    $breeder->farm_name = ucwords($request['farm_name']);
                    $breeder->farm_address = ucwords($request['farm_address']);
                    $breeder->chapter = ucfirst($request['chapter']);
                    $breeder->banded_cockerels = 1;
                    $breeder->save();
                } else {
                    $checkBreeder->banded_cockerels += 1;
                    $checkBreeder->save();
                }

                $checkFarm = Farm::where('name', ucwords($request['farm_name']))->first();

                if (! $checkFarm) {
                    $farm = new Farm;
                    $farm->name = ucwords($request['farm_name']);
                    $farm->address = ucwords($request['farm_address']);
                    $farm->breeder_name = ucwords($request['breeders']);
                    $farm->banded_cockerels = 1;
                    $farm->save();
                } else {
                    $checkFarm->banded_cockerels += 1;
                    $checkFarm->save();
                }

                $checkChapter = Chapter::where('chapter', ucfirst($request['chapter']))->first();

                if (! $checkChapter) {
                    $chapter = new Chapter;
                    $chapter->chapter = ucfirst($request['chapter']);
                    $chapter->banded_cockerels = 1;
                    $chapter->save();
                } else {
                    $checkChapter->banded_cockerels += 1;
                    $checkChapter->save();
                }

                $season = ModelsSeason::where('season', $seasons)->where('year', now()->year)->first();

                if (! $season) {
                    $season = new ModelsSeason;
                    $season->season = $seasons;
                    $season->entry += 1;
                    $season->year = now()->year;
                    $season->save();
                } else {
                    $season->entry += 1;
                    $season->save();
                }

                DB::commit();
            }

            return new ApiSuccessResponse(
                null,
                ['message' => 'Wingbands created successfully!'],
                Response::HTTP_CREATED
            );

        } catch (\Exception $e) {

            DB::rollBack();

            Log::error($e);

            return new ApiErrorResponse(
                'An error occured while storing wingband data.',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    public function importWingband(ImportWingbandRequest $request)
    {
        if (! $request->hasFile('excel_file')) {
            return new ApiErrorResponse(
                'No file uploaded',
                Response::HTTP_BAD_REQUEST
            );
        }

        $file = $request->file('excel_file');

        if ($file->getClientOriginalExtension() !== 'xlsx') {
            return new ApiErrorResponse(
                'Invalid file format. Only .xlsx files are allowed.',
                Response::HTTP_BAD_REQUEST
            );
        }

        try {

            DB::beginTransaction();

            Excel::import(new WingbandImport, $file);

            return new ApiSuccessResponse(
                null,
                ['message' => 'Wingbands imported successfully please check the excel data uploaded to the system'],
                Response::HTTP_CREATED
            );

        } catch (\Exception $e) {

            DB::rollBack();

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

            $wingband = Wingband::withTrashed()->find($id);

            if (! isset($wingband)) {
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

            if ($wingband->isClean()) {
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
            Log::error($e);

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

            if (! isset($wingband)) {
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
            Log::error($e);

            return new ApiErrorResponse(
                'An error occured while updating wingband data.',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
