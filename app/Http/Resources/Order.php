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
        $product = $this->product()->latest()->get(); // Product::collection($this->product()->latest()->get());
        
        if($this->type == 2){
            return [
                'id'=>$this->id,
                'date'=>$this->created_at->format('Y/m/d H:i:s'),
                'type'=>$this->type,
                'points'=>$this->points,
                'status'=>$this->getResourceStatus(),
                'messages'=> 'It\'s add value.',
            ];
        }

        if($this->type == 1){
            if($product == null || $product->first() == null){
                return [
                    'id'=>-1,
                    'messages'=> 'Programming error, please check order table.',
                ];
            }
/*             else if($product->first()==null){
                $media = $this->media()->latest()->get();
            } */
            else{
                if($product->first()->type == 0){
                    $media = collect([$product->first()->media]);
                }
                else{
                    $album = collect([$product->first()->album]);
                    $albumCollection = Album::Collection($album);
                    $media = $albumCollection->first()->media;
                }
            }
        }
        else{
            $media = $this->media()->latest()->get();
        }
        
        $processed = $media->filter(fn($item) => $item->status === 1)->count();
        $total = $media->count();

        return [
            'id'=>$this->id,
            'date'=>$this->created_at->format('Y/m/d H:i:s'),
            'type'=>$this->type,
            'status'=>$this->getResourceStatus(),
            'progress'=>$processed .'/' . $total,
            'media'=> Media::collection($media),
        ];
    }
}
