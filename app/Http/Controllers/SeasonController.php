<?php

namespace App\Http\Controllers;

use App\Models\Season;
use App\Http\Responses\ApiSuccessResponse;
use App\Http\Requests\Season\SeasonRequest;
use Symfony\Component\HttpFoundation\Response;

class SeasonController extends Controller
{      
    public function countRegistry(SeasonRequest $request)
    {
        $validated = $request->validated();

        $season = Season::where('year', $validated['year']);

        $data = [
            'data' => $season->get(),
            'total_entry' => $season->sum('entry')
        ];

        return new ApiSuccessResponse(
            $data,
            ['message' => 'Season entry count retrieved successfully!'],
            Response::HTTP_OK
        );
    }
}
