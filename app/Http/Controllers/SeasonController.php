<?php

namespace App\Http\Controllers;

use App\Models\Season;
use App\Models\Wingband;
use App\Classes\ActivityLogClass;
use App\Http\Responses\ApiSuccessResponse;
use App\Http\Requests\Season\SeasonRequest;
use Symfony\Component\HttpFoundation\Response;

class SeasonController extends Controller
{
    public function countRegistry(SeasonRequest $request)
    {
        $validated = $request->validated();

        $seasons = Season::where('year', $validated['year'])->get();

        $seasons->transform(function ($season) {
            if ($season->id == 1) {
                $earlybirdCount = Wingband::where('created_by', auth()->user()->id)->where('season', 1)->count();
                $season->entry = $earlybirdCount;
            } elseif ($season->id == 2) {
                $localCount = Wingband::where('created_by', auth()->user()->id)->where('season', 2)->count();
                $season->entry = $localCount;
            } elseif ($season->id == 3) {
                $nationalCount = Wingband::where('created_by', auth()->user()->id)->where('season', 3)->count();
                $season->entry = $nationalCount;
            } elseif ($season->id == 4) {
                $latebornCount = Wingband::where('created_by', auth()->user()->id)->where('season', 4)->count();
                $season->entry = $latebornCount;
            }

            return $season;
        });

        $data = [
            'data' => $seasons,
            'total_entry' => $seasons->sum('entry'),
        ];

        ActivityLogClass::create('Season Count Registry');

        return new ApiSuccessResponse(
            $data,
            ['message' => 'Season entry count retrieved successfully!'],
            Response::HTTP_OK
        );
    }
}
