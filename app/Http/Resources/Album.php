<?php

namespace App\Http\Resources;

use App\Models\Product;
use App\Models\AlbumDetail;
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

        
        $ex_mediaID = [];
        $ex_order = $this->user->orders()->whereHas('product_solution_order', function($orderQuery) use ($request) {
            $orderQuery->where('is_activated', 0);
        })->with([
            'product_solution_order.product_solution.product.album.albumDetail.media'
        ])->get()->each(function ($order) use (&$ex_mediaID) {
            $ex_product = $order->product_solution_order->product_solution->product;
            
            if ($ex_product->type == 1) 
                $ex_mediaID = array_merge($ex_mediaID, $ex_product->album->albumDetail->pluck('media_id')->toArray());
            elseif ($ex_product->type == 0)
                $ex_mediaID[] = $ex_product->media_id;
        });

        $detail_media = $this->albumDetail()->with('media')->get();
        $mediaList = $detail_media->reject(function ($item) use ($ex_mediaID) {
            return in_array($item->media->id, $ex_mediaID);
        })->pluck('media');

        return [
            'id'=>$this->id,
            'name'=>$this->name,
            'date'=>$this->created_at->format('Y/m/d H:i:s'),
            'type'=>$this->type,
            'cover'=>$this->cover? Storage::disk('s3')->temporaryUrl($this->cover, now()->addHour()) : null,
            // 'media'=> Media::collection($media),
            'media'=> Media::collection($mediaList),
        ];
    }
}
