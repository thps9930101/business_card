<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Resources\Json\JsonResource;

class Album extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $media = $this->media()->latest()->get();
        $processed = $media->filter(fn($item) => $item->status === 1)->count();
        $total = $media->count();

        return [
            'id'=>$this->id,
            'name'=>$this->name,
            'date'=>$this->created_at->format('Y/m/d H:i:s'),
            'type'=>$this->type,
            'cover'=>$this->cover? Storage::disk('s3')->temporaryUrl($this->cover, now()->addHour()) : null,
            'media'=> Media::collection($media),
        ];
    }
}
