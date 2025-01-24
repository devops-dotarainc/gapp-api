<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QueryLog
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (env('ENABLE_QUERY_LOG', false) && env('APP_ENV') != 'production') {
            DB::enableQueryLog();
        }

        $response = $next($request);

        if ($response->getStatusCode() >= 500) {
            return $response;
        }

        if (env('ENABLE_QUERY_LOG', false) && env('APP_ENV') != 'production') {
            $queries['mysql'] = DB::getQueryLog();

            $total_time = 0;
            $total_count = 0;
            foreach ($queries['mysql'] as &$query) {
                $query['query'] = str_replace('"', "'", $query['query']);
                $total_time += $query['time'];
                $total_count++;
            }
            array_push($queries['mysql'], ['total_time' => $total_time]);
            array_push($queries['mysql'], ['total_count' => $total_count]);

            $content = json_decode($response->getContent(), true);

            if (is_array($content) && count($content) > 0 && isset($content[0]) && is_array($content[0])) {
                $content[]['query_log'] = $queries;
            } else {
                $content['query_log'] = $queries;
            }

            $response->setContent(json_encode($content));
        }

        // if (auth()->user() && auth()->user()->role == 'player') {
        //     if ($request->path() == "api/auth/profile" && $request->method() == "GET") {
        //         return $response;
        //     }
        //     sleep(2);
        // }

        return $response;
    }
}
