<?php

namespace App\Http\Controllers;

use App\Http\Requests\Summary\ChapterRequest;
use App\Http\Responses\ApiSuccessResponse;
use App\Models\Season;
use Symfony\Component\HttpFoundation\Response;

class SeasonController extends Controller
{      
    public function countRegistry(ChapterRequest $request)
    {
        $validated = $request->validated();

        $season = Season::where('season', $validated['season']);

        if(isset($validated['year'])) {
            $season->where('season', $request->season);
        }

        $data = [
            'count' => $season->count()
        ];

        return new ApiSuccessResponse(
            $data,
            ['message' => 'Season entry count retrieved successfully!'],
            Response::HTTP_OK
        );
    }
}
