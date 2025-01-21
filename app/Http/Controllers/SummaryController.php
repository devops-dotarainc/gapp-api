<?php

namespace App\Http\Controllers;

use Symfony\Component\HttpFoundation\Response;
use App\Helpers\Cryptor;
use App\Http\Responses\{
    ApiErrorResponse, 
    ApiSuccessResponse
};
use App\Http\Requests\Summary\{
    BreederRequest,
    ChapterRequest,
    FarmsRequest,
    StagRequest
};
use App\Models\Breeder;
use App\Models\Chapter;
use App\Models\Farm;
use App\Models\Stag;

class SummaryController extends Controller
{    
    public function getBreeders(BreederRequest $request)
    {
        $limit = $request->limit ?? 50;

        $sort = $request->sort ?? 'id';

        $order = $request->order ?? 'asc';

        $breeders = new Breeder();

        if(isset($request->search)) {

            $search = $request->search;

            $breeders->where('name', 'LIKE', "%$search%")
                ->orWhere('farm_name', 'LIKE', "%$search%")
                ->orWhere('farm_address', 'LIKE', "%$search%")
                ->orWhere('chapter', 'LIKE', "%$search%");
        }

        if($breeders->doesntExist()) {
            return new ApiErrorResponse(
                'No breeders found.',
                Response::HTTP_NOT_FOUND
            );
        }

        $data = $breeders->orderBy($sort, $order)->paginate($limit);

        $data->getCollection()->transform(function ($breeder) {
            $breeder->_id = Cryptor::encrypt($breeder->id);
            return $breeder;
        });

        return new ApiSuccessResponse(
            $data,
            ['message' => 'Breeders retrieved successfully!'],
            Response::HTTP_OK
        );
    }

    public function getChapters(ChapterRequest $request)
    {
        $limit = $request->limit ?? 50;

        $sort = $request->sort ?? 'id';

        $order = $request->order ?? 'asc';

        $chapters = new Chapter();

        if(isset($request->search)) {
            $search = $request->search;

            $chapters->where('chapter', 'LIKE', "%$search%");
        }

        if($chapters->doesntExist()) {
            return new ApiErrorResponse(
                'No chapters found.',
                Response::HTTP_NOT_FOUND
            );
        }

        $data = $chapters->orderBy($sort, $order)->paginate($limit);

        $data->getCollection()->transform(function ($chapter) {
            $chapter->_id = Cryptor::encrypt($chapter->id);
            return $chapter;
        });

        return new ApiSuccessResponse(
            $data,
            ['message' => 'Chapters retrieved successfully!'],
            Response::HTTP_OK
        );
    }

    public function getFarms(FarmsRequest $request)
    {
        $limit = $request->limit ?? 50;

        $sort = $request->sort ?? 'id';

        $order = $request->order ?? 'asc';

        $farms = new Farm();
        
        if(isset($request->search)) {
            $search = $request->search;

            $farms->where('name', 'LIKE', "%$search%")
                ->orWhere('address', 'LIKE', "%$search%")
                ->orWhere('breeder_name', 'LIKE', "%$search%");
        }

        if($farms->doesntExist()) {
            return new ApiErrorResponse(
                'No farms found.',
                Response::HTTP_NOT_FOUND
            );
        }

        $data = $farms->orderBy($sort, $order)->paginate($limit);

        $data->getCollection()->transform(function ($farms) {
            $farms->_id = Cryptor::encrypt($farms->id);
            return $farms;
        });

        return new ApiSuccessResponse(
            $data,
            ['message' => 'Farms retrieved successfully!'],
            Response::HTTP_OK
        );
    }

    public function getStags(StagRequest $request)
    {
        $limit = $request->limit ?? 50;

        $sort = $request->sort ?? 'id';

        $order = $request->order ?? 'asc';

        $stags = new Stag();

        if(isset($request->search)) {
            $search = $request->search;

            $stags->where('stag_registry', 'LIKE', "%$search%")
                ->orWhere('farm_name', 'LIKE', "%$search%")
                ->orWhere('farm_address', 'LIKE', "%$search%")
                ->orWhere('breeder_name', 'LIKE', "%$search%")
                ->orWhere('chapter', 'LIKE', "%$search%");
        }

        if($stags->doesntExist()) {
            return new ApiErrorResponse(
                'No stags found.',
                Response::HTTP_NOT_FOUND
            );
        }

        $data = $stags->orderBy($sort, $order)->paginate($limit);

        $data->getCollection()->transform(function ($stag) {
            $stag->_id = Cryptor::encrypt($stag->id);
            return $stag;
        });

        return new ApiSuccessResponse(
            $data,
            ['message' => 'Breeders retrieved successfully!'],
            Response::HTTP_OK
        );
    }

    public function getStatistics(){
        $user = auth()->user();

        $chapterCount = Chapter::count();
        $farmCount = Farm::count();
        $stagCount = Stag::count();
        $breederCount = Breeder::count();

        return new ApiSuccessResponse(
            [
                "Chapter" => $chapterCount,
                "Farm" => $farmCount,
                "Stag" => $stagCount,
                "Breeder" => $breederCount,
            ],
            ['message' => 'Summary statistics retrieved successfully!'],
            Response::HTTP_OK
        );
    }
}
