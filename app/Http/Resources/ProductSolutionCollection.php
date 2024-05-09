<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ProductSolutionCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return[
            'data' => $this->collection,
/*             'media'=> Media::collection($media), */
/*             'links'=> [
                'self' => $this->currentPage(),
                'next' => $this->nextPageUrl(),
                'prev' => $this->previousPageUrl(),
            ],
            'meta'=> [
                'hasNext' => $this->hasMorePages(),
                'total'=> $this->total(),
            ], */
        ];
    }
}
