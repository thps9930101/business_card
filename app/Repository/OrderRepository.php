<?php
namespace App\Repository;

use App\Models\Media;
use App\Models\Order;
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
        return $this;
    }

    public function setMedia($media){
        $this->media = $media;
        return $this;
    }

    public function getMedia(){
        return $this->media;
        return $this;
    }


    public function userUploadMedia($request){
        DB::transaction(function () use ($request) {
            $order = $this->order;
            $user = $request->user();
            $order->user_id = $user->id;
            $order->save();
            $media = new Media;
            $media->order_id = $order->id;
            $media->name = '3d圖片';
            $media->user_id = $user->id;
            $media->type = 1;
            $media->save();
            $path = $request->file('pic')->store($this->getOrigianlFolderPath($media->id),'s3');
            $media->obj = $path;
            //resize image to cover
            $fileName = $request->file('pic')->getClientOriginalName();
            $image = Image::make($request->file('pic'));
            $image->widen(300, function ($constraint) {
               // $constraint->aspectRatio();
                $constraint->upsize();
            });
            $imageStream = $image->stream();
            $coverPath = env('APP_ENV')."/$user->id/$order->id/$media->id/cover/".$fileName;
            Storage::disk('s3')->put($coverPath, $imageStream->__toString());
            $media->cover = $coverPath;

            $media->save();

            $this->media = $media;

        });

        return $this;
    }

    public function userUploadMediaFromCanvas($request){
        DB::transaction(function () use ($request) {
            $order = $this->order;
            $user = $request->user();
            $order->user_id = $user->id;
            $order->save();
            $media = new Media;
            $media->order_id = $order->id;
            $media->name = '3d圖片';
            $media->user_id = $user->id;
            $media->type = 1;
            $media->save();
            $file = $this->dataUrlToFile($request->pic);
            //resize image to cover
            $fileName = $file->getClientOriginalName();
            $image = Image::make($file);
            $path = $file->store($this->getOrigianlFolderPath($media->id),'s3');
            $media->obj = $path;
            $image->widen(300, function ($constraint) {
               // $constraint->aspectRatio();
                $constraint->upsize();
            });
            $imageStream = $image->stream();
            $coverPath = env('APP_ENV')."/$user->id/$order->id/$media->id/cover/".$fileName;
            Storage::disk('s3')->put($coverPath, $imageStream->__toString());
            $media->cover = $coverPath;

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
