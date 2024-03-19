<?php

namespace App\Events;

use App\Repository\OrderRepository;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Collection;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

use App\Models\Media;
use App\Http\Resources\Media as MediaResource;
use App\Http\Resources\MediaCollection;

class PicUploaded  implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $id;
    public $name;
    public $obj;
    public $path;
    public $mediaCollection;

    /**
     * Create a new event instance.
     */
    public function __construct($video)
    {
        $this->id=$video->id??'null';
        $this->name=$video->name??'null';
        $this->obj=isset($video->obj)?Storage::disk('s3')->temporaryUrl($video->original??$video->obj, now()->addHour()) : 'null';
        $this->path=isset($vide->order)?(new OrderRepository($video->order))->getPath($video->id) : 'null';

        if ($video->id)
        {
            $media = Media::Where('id', $video->id)->latest()->get();
            $mCollection = MediaResource::collection($media);

            $this->mediaCollection = $mCollection->toJson();
        }
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('service'),
            new Channel('user.'.auth()->user()->id),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'pic.uploaded';
    }
}
