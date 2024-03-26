<?php
namespace App\Repository;

use FFMpeg\FFMpeg;
use Carbon\Carbon;
use App\Models\Media;
use App\Models\Order;
use App\Models\Plan_solution;
use App\Models\Plan_solution_order;
use App\Models\Product_solution;
use App\Models\Product_solution_order;
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
        $order->type = $request->type ? $request->type : 0;
        $order->product_id = $request->product_id ? $request->product_id : null;
        /* $order->points = $request->points ? $request->points : 0;
        $order->free_points = $request->free_points ? $request->free_points : 0; */
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

    // add by jason
    public function createPlanSolutionOrder($request){
        $solution_order = new Plan_solution_order;
        $solution_order->order()->associate($this->order);
        $solution_order->plan_solution()->associate(Plan_solution::where('id', $request->plan_solution['id'])->first()); 
        $solution_order->expired_at = Carbon::now()->addMonths($request->plan_solution['period']);
        $solution_order->is_activated = true;
        $solution_order->save();
        // $this->solution_order = $solution_order;
        return $solution_order;
    }

    public function createProductSolutionOrder($product_solution, $isFree){
        $solution_order = new Product_solution_order;
        $solution_order->order()->associate($this->order);
        $solution_order->product_solution()->associate(Product_solution::where('id', $product_solution['id'])->first()); 
        
        if (!$isFree) {
            // $solution_order->expired_at = Carbon::now()->addMonths($product_solution['period']);
            $solution_order->expired_at = Carbon::now()->addYear($product_solution['period']);
        }
        else {
            $solution_order->expired_at = Carbon::now()->addYear(99);
        }

        if($product_solution['period'] < 1){
            $solution_order->next_expired_at = Carbon::now()->addMonths($product_solution['period']);
        }
        else{
            $solution_order->next_expired_at = Carbon::now()->addMonths(1);
        }
        $solution_order->is_activated = true;
        $solution_order->save();
        // $this->solution_order = $solution_order;
        return $solution_order;
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

    public function userAddValueSucc($request){
        DB::transaction(function () use ($request) {
            $order = $this->order;
            $order->status = 2;

            $target = 'points';
            
            if($request->to){
                $target = $request->to;
            }
            
            $order->$target = (int)$request->value;
            $order->save();
        });

        return $this;
    }
            
    /**
     * add value failed
     * 1 create order
     */
    public function userAddValueFailed($request){
        DB::transaction(function () use ($request) {
            $order = $this->order;
            $order->status = 3;  // failed status
            $target = $request->to;
            $order->$target = $request->value;
            $order->save();
        });

        return $this;
    }
    /**
     * subcscirbe a product
     * 1 create order
     * 2 create product_solution_order
     * 3 
     */
    // add by jason
    public function subscribePlan($request){
        DB::transaction(function () use ($request) {
            $this->createOrder($request);
            $solution_order = $this->createPlanSolutionOrder($request);
            $this->solution_order = $solution_order;
        });

        return $this;
    }
    /**
     * subcscirbe a product
     * 1 create order
     * 2 create product_solution_order
     * 3 
     */
    public function subscribeProduct($request){
        DB::transaction(function () use ($request) {
            $this->createOrder($request);
            $solution_order = $this->createProductSolutionOrder($request->product_solution, false);
            $this->solution_order = $solution_order;
        });

        return $this;
    }

    public function subscribeProductFree($request, $product_solution){
        DB::transaction(function () use ($request, $product_solution) {
            $this->createOrder($request);
            $solution_order = $this->createProductSolutionOrder($product_solution, true);
            $this->solution_order = $solution_order;
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

            foreach ($request->pic as $pic) {
                $media = $this->createMedia();
                $file = $this->dataUrlToFile($pic);
                //resize image to cover
                $media->cover = $this->imageToCover($file);
                $path = $file->store($this->getOrigianlFolderPath($media->id),'s3');
                $media->obj = $path;
                $media->original = $path;
                $media->save();
                $this->media = $media;
            }

            // $i = 0;
            // foreach ($request->pic as $pic) {
            //     $media = $this->createMedia();
            //     $file_corp = $this->dataUrlToFile($pic);
            //     // //resize image to cover
            //     // $media->cover = $this->imageToCover($file);
            //     // $path = $file->store($this->getOrigianlFolderPath($media->id),'s3');

            //     // // save original pic
            //     // $file_origin = $this->dataUrlToFile($request->origin_pic[$i]);
            //     // $path_crop = $file_origin->store($this->getCropFolderPath($media->id),'s3');

            //     // crop 版本
            //     $file_origin = $this->dataUrlToFile($request->origin_pic[$i]);
            //     $path = $file_origin->store($this->getOrigianlFolderPath($media->id),'s3');

            //     // save original pic
            //     $media->cover = $this->imageToCover($file_corp);
            //     $path_crop = $file_corp->store($this->getCropFolderPath($media->id),'s3');

            //     $media->obj = $path_crop;
            //     $media->original = $path;
            //     $media->crop = $path_crop;
            //     $media->save();
            //     $this->media = $media;

            //     $i++;
            // }
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
        // $image->getCore()->trimImage(0);
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

    public function getCropFolderPath($mediaId=null){
        $mediaId = $mediaId?? $this->order->media->first()->id;
        return env('APP_ENV')."/".$this->order->user->id."/".$this->order->id."/$mediaId/crop";
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
