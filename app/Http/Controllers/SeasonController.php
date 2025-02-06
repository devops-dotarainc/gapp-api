<?php

namespace App\Http\Controllers;

use App\Models\Season;
use App\Models\Wingband;
use App\Classes\ActivityLogClass;
use App\Enums\Role;
use App\Http\Responses\ApiSuccessResponse;
use App\Http\Requests\Season\SeasonRequest;
use Symfony\Component\HttpFoundation\Response;

class SeasonController extends Controller
{
    public function countRegistry(SeasonRequest $request)
    {
        $validated = $request->validated();

        if(isset($validated['year'])) {
            $seasons = Season::select('season', 'year', 'entry')
            ->orderBy('season', 'asc')
            ->where('year', $validated['year'])
            ->get();
        } else {
            $seasons = Season::selectRaw('season, SUM(entry) as entry')
            ->orderBy('season', 'asc')
            ->groupBy('season')
            ->get()
            ->map(function ($season) {
                $season->entry = (int) $season->entry;

                return $season;
            });
        }

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
