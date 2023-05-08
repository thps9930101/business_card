<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Resources\Json\JsonResource;

class Media extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'=>$this->id,
            'type'=>$this->type,
            'cover'=> $this->status && $this->cover? Storage::disk('s3')->temporaryUrl($this->cover, now()->addHour()) : null,
            'obj'=> $this->status && $this->obj? Storage::disk('s3')->temporaryUrl($this->obj, now()->addHour()): null,
            'status'=>$this->status,
            'name'=>$this->name,
        ];
    }
}
