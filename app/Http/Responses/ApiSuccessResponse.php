<?php

namespace App\Http\Responses;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Response;

class ApiSuccessResponse implements Responsable
{
    /**
     * @param  mixed  $data
     */
    public function __construct(
        // private mixed $data,
        private array $metadata,
        private int $code = Response::HTTP_OK,
        private array $headers = []
    ) {}

    /**
     * @return \Symfony\Component\HttpFoundation\Response|void
     */
    public function toResponse($request)
    {
        return response()->json(
            [
                // 'data' => $this->data,
                'metadata' => $this->metadata,
            ],
            $this->code,
            $this->headers
        );
    }
}
