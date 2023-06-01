<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Repository\OrderRepository;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\OrderCollection;
use Illuminate\Support\Facades\Validator;

class StaffController extends Controller
{

    /**
     * staff login
     */
    public function login(Request $request){
        $validator = Validator::make($request->all(),[
            'email' => 'required|email|exists:users,email',
            'password' => 'required'
        ]);

        if($validator->fails()){
            return [
                'success' => false,
                'message' => __('auth.failed'),
                'errors'=> $validator->errors()->toArray()
            ];
        }
        $credentials = $request->only('email', 'password');

        //if user is not admin return error
        if(!User::where('email',$request->email)->where('is_admin',1)->exists()){
            return [
                'success' => false,
                'message' => __('auth.not_admin'),
                'errors'=> ['email'=>[__('auth.not_admin')]]
            ];
        }

        if ($result = Auth::attempt($credentials)) {
            $auth = Auth::user();

            return [
                'success' => true,
                'message' => [
                    'id' =>  $auth->id,
                    'name' =>  $auth->name,
                    'email' =>  $auth->email,
                    'token'=>  $auth->createToken($request->email)->plainTextToken]
            ];
        }else{
            return [
                'success' => false,
                'message' => __('auth.failed'),
                'errors'=> $result
            ];
        }
    }

    /**
     * addOrderList
     */
    public function addOrderList(Request $request){

        $validator = Validator::make($request->all(),[
            'memberId' => 'required|exists:users,id',
        ]);

        if($validator->fails()){
            return [
                'success' => false,
                'message' => __('staff.no_member'),
                'errors'=> $validator->errors()->toArray()
            ];
        }

        $user = User::find($request->memberId);

        $order =$user->orders()->create([
            'status'=>0
        ]);

        return [
            'success' => true,
            'message' => [
                'id'=>$order->id,
                'date'=>$order->created_at->format('Y-m-d H:i:s'),
                'status'=>$order->status,
                'memberId'=>$user->id,
            ]
        ];

    }

    /**
     * add video
     */
    public function addVideo(Request $request){
        $validator = Validator::make($request->all(),[
            'orderListId' => 'required|exists:orders,id',
        ]);

        if($validator->fails()){
            return [
                'success' => false,
                'message' => __('staff.no_order'),
                'errors'=> $validator->errors()->toArray()
            ];
        }

        $order = Order::find($request->orderListId);
        if(!$order->user->id){
            return [
                'success' => false,
                'message' => __('staff.order_not_member'),
                'errors'=> $validator->errors()->toArray()
            ];
        }

        $repo = new OrderRepository($order);

        //create media
        /* ATTENTION: don't add original property when creating empty media. Because all media that has original attribute with status =0 will be fetched by the 3d graph server */
        $media = $order->media()->create([
            'type'=>0,
            'status'=>0,
            'user_id'=>$order->user_id,
            'is_staff_uploaded'=>1
        ]);

        if($media){
            return [
                'success' => true,
                'message' => [
                    'id'=>$media->id,
                    'date'=>now(),
                    'name'=>'3D Video',
                    'type'=>'video',
                    'status'=>$media->status,
                    'finish_time'=>null,
                    'iconPath'=>$repo->getVideoCoverPath($media->id),
                    'videoPath'=>$repo->getVideoPath($media->id),
                ]
            ];
        }


    }

    public function queryAllOrderList(Request $request){

        return [
            'success'=>true,
            'message'=>new OrderCollection (Order::latest()->paginate(999999)),
        ];
    }

}
