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

        if(auth()->user()->role != Role::ENCODER && !isset($validated['encoder'])) {
            if(isset($validated['year'])) {
                $seasons = Season::select('season', 'year', 'entry')
                ->where('year', $validated['year'])
                ->orderBy('season', 'asc')
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
        } else {
            $seasons = Wingband::selectRaw('season, COUNT(id) as entry');

            if(auth()->user()->role == Role::ENCODER) {
                $seasons->where('created_by', auth()->user()->id);
            }

            if(isset($validated['year'])) {
                $seasons->whereYear('wingband_date', $validated['year']);
            }

            if(isset($validated['encoder'])) {
                $seasons->where('created_by', intval($validated['encoder']));
            }
            
            $seasons = $seasons->groupBy('season')
            ->orderBy('season', 'asc')
            ->get();
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
