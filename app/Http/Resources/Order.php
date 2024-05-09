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
        $product_solution_order = $this->product_solution_order()->latest()->get(); // Product::collection($this->product()->latest()->get());
        
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
        $isAlbum = false;

        if($this->type == 1){        
            if (count($product_solution_order) == 0) {
                $media = collect([]);
            }
            else
            {
                if($product_solution_order->first()->status == 0){
                    // 單張
                    if($product_solution_order->first()->product_solution->product->type == 0){
                        $media = collect([$product_solution_order->first()->product_solution->product->media]);
                    }
                    // 相簿
                    else{
                        $album = collect([$product_solution_order->first()->product_solution->product->album]);
                        $albumCollection = Album::Collection($album);
                        $media = $albumCollection->first()->media;

                        $copyAlbumCollection = json_decode(json_encode($albumCollection->first()));
                        // $media = collect([$albumCollection->first()->media]);
                        $isAlbum = true;
                    }
                }  
                else{
                    $media = collect([]);
                }
            }
        }
        else{
            $media = $this->media()->latest()->get();
        }
        
        $processed = $media->filter(fn($item) => $item->status === 1)->count();
        $total = $media->count();

        if ($isAlbum) 
            $media = collect($copyAlbumCollection->media);
        else 
            $media = Media::collection($media);

        return [
            'id'=>$this->id,
            'date'=>$this->created_at->format('Y/m/d H:i:s'),
            'type'=>$this->type,
            'status'=>$this->getResourceStatus(),
            'progress'=>$processed .'/' . $total,
            // 'media'=> Media::collection($media),
            'media'=>$media,
        ];
    }
}
