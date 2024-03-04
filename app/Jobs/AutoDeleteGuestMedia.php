<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

use App\Models\Media;
use App\Events\PicUploadFailed;

class AutoDeleteGuestMedia implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $mediaID;
    /**
     * Create a new job instance.
     */
    public function __construct($mediaID)
    {
        $this->mediaID = $mediaID;
        // Log::info('ID: '.$this->mediaID);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // define("STATUS_DELETED", 3);
        $STATUS_DELETED = 3;
        $media = Media::find($this->mediaID);
        if ($media->exists())
        {
            $media->status = $STATUS_DELETED;

            //delete original file
            if ($media->original && Storage::disk('s3')->exists($media->original)) {
                Storage::disk('s3')->delete($media->original);
            }
    
            //delete obj file
            if ($media->obj && Storage::disk('s3')->exists($media->obj)) {
                Storage::disk('s3')->delete($media->obj);
            }
            
            //delete cover file
            if ($media->cover && Storage::disk('s3')->exists($media->cover)) {
                Storage::disk('s3')->delete($media->cover);
            }
            $media->delete();
            event(new PicUploadFailed(null));
        }
    }
}
