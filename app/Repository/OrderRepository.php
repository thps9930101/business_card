<?php
namespace App\Repository;

use App\Models\Media;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class OrderRepository
{
    protected $order;

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
    public function userUploadMedia($request){
        DB::transaction(function () use ($request) {
            $order = $this->order;
            $user = $request->user();
            $order->user_id = $user->id;
            $order->save();

            $media = new Media;
            $media->order_id = $order->id;
            $media->user_id = $user->id;
            $media->type = 1;
            $path = $request->file('pic')->store(env('APP_ENV')."/$user->id/$order->id/$media->id/obj",'s3');
            $media->obj = $path;
            $media->save();
        });

        return $this;
    }

}
