<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class Order extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $media = $this->media()->get();
        $processed = $media->filter(fn($item) => $item->status === 1)->count();
        $total = $media->count();

        return [
            'id'=>$this->id,
            'date'=>$this->created_at->format('Y-m-d H:i:s'),
            'type'=>$this->type,
            'status'=>$this->status,
            'progress'=>$processed .'/' . $total,
            'media'=> Media::collection($media)
        ];
    }
}
