<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class Product extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $store = $this->store()->latest()->get();
        
        if ($this->type == 0)
            $media = $this->media()->latest()->get();
        else {
            // 有detail
            $media = $this->album->albumDetail()->with('media')->get()->pluck('media');

            // 沒detail
            // $media = collect($this->album()->get()->first()->media);
        }
        $hasFreeProductSolution = $this->product_solution()->where('costs', '=', 0)->where('is_activated', 1)->exists();

        return [
            'id'=>$this->id,
            'hasFree' => $hasFreeProductSolution,
            'store'=> Store::collection($store),
            'costs'=>$this->costs,
            'type'=>$this->type,
            'media'=> Media::collection($media),
            'album'=> $this->album_id,
            'is_activated'=>$this->is_activated,
            'featured'=>$this->featured,
        ];
    }
}
