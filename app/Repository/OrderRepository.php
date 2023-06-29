<?php
namespace App\Repository;

use FFMpeg\FFMpeg;
use App\Models\Media;
use App\Models\Order;
use FFMpeg\Coordinate\TimeCode;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;

class OrderRepository
{
    protected $order;

    protected $media;

    public function __construct($order= null){
        $this->order = $order?? new Order;
    }

    public function setOrder($order){
        $this->order = $order;
        return $this;
    }
    public function getOrder(){
        return $this->order;

    }

    public function setMedia($media){
        $this->media = $media;
        return $this;
    }

    public function getMedia(){
        return $this->media;

    }

    public function createOrder($request){
        $order = $this->order;
        $user = $request->user();
        $order->user()->associate($user);
        $order->save();
        return $order;
    }

    public function createMedia($type=1){
        $media = new Media;
        $media->order()->associate($this->order);
        $media->user()->associate($this->order->user);
        $media->type = $type;
        $media->save();
        $this->media = $media;
        return $media;
    }

    /**
     * transform image to cover size and save to s3
     * @param $mediaId
     * @return string
     */

    public function imageToCover($file){
        $image = Image::make($file);
        $image->widen(300, function ($constraint) {
           // $constraint->aspectRatio();
            $constraint->upsize();
        });
        $imageStream = $image->stream();
        $coverPath = $this->getCoverFolderPath($this->media->id).'/'.$file->getClientOriginalName();
        Storage::disk('s3')->put($coverPath, $imageStream->__toString());
        return $coverPath;

    }
    /**
     * extract frame from video and make it uploaded file
     * @param $video
     * @return UploadedFile
     */

    public function getFrameFileFromVideo($video){
        $ffmpeg = FFMpeg::create();
        $video = $ffmpeg->open($video);
        $frame = $video->frame(TimeCode::fromSeconds(2));
        //save frame to temp
        $frame->save('/tmp/frame.jpg');
        //transfer frame to s3
        $frameFile = new UploadedFile('/tmp/frame.jpg', 'frame.jpg', 'image/jpeg', null, true);
        return $frameFile;
    }

    public function userUploadVideo($request){
        DB::transaction(function () use ($request) {
            $this->createOrder($request);
            $media = $this->createMedia(0);
            //cover image
            $file = $request->file('video');
            $frameFile = $this->getFrameFileFromVideo($file);
            $coverPath = $this->imageToCover($frameFile);
            $path = $file->store($this->getOrigianlFolderPath($media->id),'s3');
            $media->obj = $path;
            $media->original = $path;
            $media->cover = $coverPath;
            $media->save();
            $this->media = $media;
        });
        return $this;
    }


    public function userUploadMedia($request){
        DB::transaction(function () use ($request) {
            $this->createOrder($request);
            $media = $this->createMedia();
            $path = $request->file('pic')->store($this->getOrigianlFolderPath($media->id),'s3');
            $media->obj = $path;
            $media->original = $path;
            //resize image to cover
            $media->cover = $this->imageToCover($request->file('pic'));

            $media->save();

            $this->media = $media;

        });

        return $this;
    }

    /**
     * upload media from canvas
     * 1 create order
     * 2 create media
     * 3 get file from data url
     * 4 resize image to cover
     * 5 save image to s3
     * 6 save media to db
     */

    public function userUploadMediaFromCanvas($request){
        DB::transaction(function () use ($request) {
            $this->createOrder($request);
            $media = $this->createMedia();
            $file = $this->dataUrlToFile($request->pic);
            //resize image to cover
            $media->cover = $this->imageToCover($file);
            $path = $file->store($this->getOrigianlFolderPath($media->id),'s3');
            $media->obj = $path;
            $media->original = $path;
            $media->save();
            $this->media = $media;
        });

        return $this;
    }

    private function dataUrlToFile($dataUrl)
    {
        $data = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $dataUrl));
        $tmpFilePath = sys_get_temp_dir() . '/' . uniqid() . '.png';
        file_put_contents($tmpFilePath, $data);
        $image = Image::make($tmpFilePath);
        // Access underlying Imagick instance and trim image
        $image->getCore()->trimImage(0);
        $image->save($tmpFilePath);

        return new \Illuminate\Http\UploadedFile($tmpFilePath, 'image.png', 'image/png', null, true);
    }

    public function getPath($mediaId=null){
        $mediaId = $mediaId?? $this->order->media->first()->id;
        return env('APP_ENV')."/".$this->order->user->id."/".$this->order->id."/$mediaId/obj/$mediaId.png";
    }

    public function getVideoPath($mediaId=null){
        $mediaId = $mediaId?? $this->order->media->first()->id;
        return env('APP_ENV')."/".$this->order->user->id."/".$this->order->id."/$mediaId/obj/$mediaId.mp4";
    }

    public function getVideoCoverPath($mediaId=null){
        $mediaId = $mediaId?? $this->order->media->first()->id;
        return env('APP_ENV')."/".$this->order->user->id."/".$this->order->id."/$mediaId/cover/$mediaId.jpg";
    }

    public function getOrigianlFolderPath($mediaId=null){
        $mediaId = $mediaId?? $this->order->media->first()->id;
        return env('APP_ENV')."/".$this->order->user->id."/".$this->order->id."/$mediaId/original";
    }

    public function getObjFolderPath($mediaId=null){
        $mediaId = $mediaId?? $this->order->media->first()->id;
        return env('APP_ENV')."/".$this->order->user->id."/".$this->order->id."/$mediaId/obj";
    }


    public function getCoverFolderPath($mediaId=null){
        $mediaId = $mediaId?? $this->order->media->first()->id;
        return env('APP_ENV')."/".$this->order->user->id."/".$this->order->id."/$mediaId/cover";
    }

}
