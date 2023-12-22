<?php

namespace App\Http\Controllers;

use Recaptcha;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Media;
use App\Models\Order;
use App\Models\Store;
use App\Models\Album;
use App\Models\Project;
use App\Models\Product;
use App\Models\Product_solution;
use App\Models\Product_solution_order;
use App\Events\PicUploaded;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Events\PicUploadFailed;
use App\Repository\OrderRepository;
use App\Events\CompleteTransformPic;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Events\CompleteTransformVideo;
use App\Notifications\ConfirmUserCode;
use App\Http\Resources\MediaCollection;
use App\Http\Resources\OrderCollection;
use App\Http\Resources\AlbumCollection;
use App\Http\Resources\StoreCollection;
use App\Http\Resources\ProductCollection;
use App\Http\Resources\ProductSolutionCollection;
use Illuminate\Support\Facades\Storage;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;


class ApiController extends Controller
{
    public function get_cpu_usage(){
        exec('top -b -n 1 | grep "Cpu(s)"', $output);
        $cpuInfo = explode(",", $output[0]);
        $cpuUsage = trim(str_replace("Cpu(s):", "", $cpuInfo[0]));

        return $cpuUsage;
    }

    /**
     * login
     */
    public function login(Request $request){

        $validator = Validator::make($request->all(),[
            'email' => 'required|email',
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
     * register
     */
    public function register(Request $request){

        $validator = Validator::make($request->all(),[
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed',
            'recaptcha'=>'required'
        ]);

        if(!Recaptcha::check()){
            $validator->errors()->add('recaptcha', __('validation.recaptcha'));
        }

        if($validator->fails()){
            return [
                'success' => false,
                'message' => __('register.failed'),
                'errors'=> $validator->errors()->toArray()
            ];
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);

        if($user ){
            $code = Str::random(60);
            $user->confirm_code = $code;
            $user->confirm_code_expired_at = now()->addDays(7);
            $user->save();
            if(app()->environment('production')){
                $user->notify(new ConfirmUserCode($code));
            }
        }

        return [
            'success' => true,
            'message' => [
                'id' =>  $user->id,
                'name' =>  $user->name,
                'email' =>  $user->email,
                'token'=>  $user->createToken($request->email)->plainTextToken,
                'confirm_url'=>  route('registerMember', ['code'=>$user->confirm_code])]
        ];



    }

    /**
     * sendConfirmEmail
     */
    public function sendConfirmEmail(Request $request){

        $user = Auth::user();

        if($user->confirm_code_expired_at < now()){
            $code = Str::random(60);
            $user->confirm_code = $code;
            $user->confirm_code_expired_at = now()->addDays(7);
            $user->save();
        }

        $user->notify(new ConfirmUserCode($user->confirm_code));

        return [
            'success' => true,
            'message' => __('register.resent')
        ];
    }

    /**
     * forget password
     */
    public function forgetPassword(Request $request){

        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status == Password::RESET_LINK_SENT
                    ? [
                        'success'=>true,
                        'message'=>__('passwords.sent')
                    ]
                    : [
                        'success'=>false,
                        'message'=>'電子郵件發送失敗！'
                        ,'error'=>__($status)
                    ];

    }

    /**
     * confirm mail
     */
    public function registerMember($code){

        if($user = User::where('confirm_code', $code)->first()){

            if(now() > $user->confirm_code_expired_at){
                return [
                    'success'=>false,
                    'message'=>'認證碼過期！請重新註冊！'
                ];
            }

            $user->confirm_code = null;
            $user->confirm_code_expired_at = null;
            $user->email_verified_at = now();

            $user->save();

            return [
                'success'=>true,
                'message'=>'註冊成功！'
            ];

        };

        return [
            'success'=>false,
            'message'=>'認證碼錯誤！請重新註冊！'
        ];

    }

    /**
     * update user
     */
    public function userUpdate(Request $request){


            $validator = Validator::make($request->all(),[
                'name' => 'required',
                'phone' => 'nullable|regex:/^09\d{2}-?\d{3}-?\d{3}$/', //手機號碼
                'password' => ['nullable','confirmed','min:8'],
                'old_password'=>'nullable|required_with:password|current_password'
            ]);


            if($validator->fails()){
                return [
                    'success' => false,
                    'message' => '更新用戶資料失敗，請檢查輸入資料',
                    'errors'=> $validator->errors()->toArray()
                ];
            }

            $user = Auth::user();
            $user->name = $request->name;

            if($request->phone){
                $user->phone = $request->phone;
            }

            if($request->password){
                $user->password = Hash::make($request->password);
            }

            $user->save();

            return [
                'success'=>true,
                'message'=>'update user success!',
            ];
    }

    /**
     * update member name
     */
    public function updateMemberName(Request $request){

        $validator = Validator::make($request->all(),[
            'name' => 'required|string',
        ]);

        if($validator->fails()){
            return [
                'success' => false,
                'message' => '更新用戶名稱失敗，請檢查輸入資料',
                'errors'=> $validator->errors()->toArray()
            ];
        }

        $user = Auth::user();
        $user->name = $request->name;
        $user->save();

        return [
            'success'=>true,
            'message'=>'update member name success!'
        ];
    }

    /**
     * update password
     */
    public function updatePassword(Request $request){

        $validator = Validator::make($request->all(),[
            'password' => 'required|password|confirmed',
            'old_password'=>'required|current_password'
        ]);

        if($validator->fails()){
            return [
                'success' => false,
                'message' => '更新密碼失敗，請檢查輸入資料',
                'errors'=> $validator->errors()->toArray()
            ];
        }

        $user = Auth::user();
        $user->password = Hash::make($request->password);
        $user->save();

        return [
            'success'=>true,
            'message'=>'update password success!'
        ];
    }

    /**
     * query order list
     */
    public function queryOrderList(){

        return [
            'success'=>true,
            'message'=>new OrderCollection (Order::where('user_id', Auth::id())->latest()->paginate(999999)),
        ];
    }

    /**
     * update video name
     */
    public function updateVideoName(Request $request,$id){

        $validator = Validator::make($request->all(),[
            'name' => 'required',
            'id'=>'required|exists:media,id'
        ]);

        if($video = Media::find($id)){
            $video->name = $request->name;
            $video->save();
        };

        return [
            'success'=>true,
            'message'=>'update video name success!'
        ];
    }

    /**
     * upload video
     */
    public function uploadVideo(Request $request){
        try{
            $validator = Validator::make($request->all(),[
                'video' => 'required|mimes:mp4,mov,ogg,qt|max:1048576', // aaaa
            ]);

            if($validator->fails()){
                return [
                    'success' => false,
                    'message' => '上傳影片失敗，請檢查輸入資料',
                    'errors'=> $validator->errors()->toArray()
                ];
            }

            $cpuUsage = $this->get_cpu_usage();

            if($cpuUsage>7){
                return[
                    'success'=>false,
                    'message'=>'系統忙碌中，請稍後再試 !',
                    'cpu'=>$cpuUsage,
                ];
            }

            //create new order, media and store file to storage
            $repository = new OrderRepository();
            $repository->userUploadVideo($request);

            $media = $repository->getMedia();

            event(new PicUploaded($media));

            return [
                'success'=>true,
                'message'=>'upload video success!',
                'cpu'=>$cpuUsage,
            ];
        }
        catch(e){
            return [
                'success'=>false,
                'message'=>e.message,
            ];
        }
    }

    /**
     * upload picture
     */
    public function uploadPicture(Request $request){
        try{
            $validator = Validator::make($request->all(),[
                'pic' => 'required|image|mimes:jpeg,png,jpg,gif,svg,bmp,webp|max:20000',
            ]);

            if($validator->fails()){
                return [
                    'success' => false,
                    'message' => '上傳圖片失敗，請檢查輸入資料',
                    'errors'=> $validator->errors()->toArray()
                ];
            }

            $cpuUsage = $this->get_cpu_usage();

            if($cpuUsage>7){
                return[
                    'success'=>false,
                    'message'=>'系統忙碌中，請稍後再試 !',
                    'cpu'=>$cpuUsage,
                ];
            }

            //create new order, media and store file to storage
            $repository = new OrderRepository();
            $repository->userUploadMedia($request);

            $media = $repository->getMedia();

            event(new PicUploaded($media));

            return [
                'success'=>true,
                'message'=>'upload picture success!',
                'cpu'=>$cpuUsage,
            ];
        }
        catch(e){
            return [
                'success'=>false,
                'message'=>e.message,
            ];
        }
    }

    /**
     * upload canvas picture
     */
    public function uploadCanvas(Request $request){
        try{        
            $validator = Validator::make($request->all(),[
                'pic' => 'required', // |string
            ]);

            if($validator->fails()){
                return [
                    'success' => false,
                    'message' => '上傳圖片失敗，請檢查輸入資料',
                    'errors'=> $validator->errors()->toArray()
                ];
            }

            $cpuUsage = $this->get_cpu_usage();

            if($cpuUsage>50){
                return[
                    'success'=>false,
                    'message'=>'系統忙碌中，請稍後再試 !',
                    'cpu'=>$cpuUsage,
                ];
            }

            // =====


            //create new order, media and store file to storage
            $repository = new OrderRepository();
            $repository->userUploadMediaFromCanvas($request);

            $media = $repository->getMedia();
            
            $user = Auth::user();

            $target = 'points';
            if($request->to){
                $target = $request->to;
            }
            
            //create new order, media and store file to storage
            if($user->$target<-(int)$request->value){
                $repository->userAddValueFailed($request);

                return [
                    'success'=>false,
                    'message'=>[
                        'type'=> 'not enough points. Please add value !',
                    ]
                ];
            }

            // if succ, then add value
            $user->$target+=(int)$request->value;
            $user->save();

            event(new PicUploaded($media));

            return [
                'success'=>true,
                'message'=>'upload picture success! media id: '.$media->id,
                'cpu'=>$cpuUsage,
            ];
        }
        catch(e){
            return [
                'success'=>false,
                'message'=>e.message,
            ];
        }
    }

    /**
     * get user videos
     */
    public function videos(Request $request){

        $validator = Validator::make($request->all(),[
            'page' => 'nullable|integer',
            'type' =>['nullable','regex:/^([0-9]+|all)$/'],
        ]);
        if($validator->fails()){
            return [
                'success' => false,
                'message' => $validator->messages()->first(),
                'errors'=> $validator->errors()->toArray()
            ];
        }

        $query = Media::where('user_id', Auth::id());

        if(($request->type ||  $request->type === 0 || $request->type==='0') && $request->type != 'all'){
            $query->whereHas('order', function($orderQuery) use ($request) {
                $orderQuery->where('type', $request->type);
            });
        }

        return [
            'success'=>true,
            'message'=>new MediaCollection ($query->paginate(10)),
          /*   'sql'=>$query->toSql(),
            'bindings'=>$query->getBindings() */
        ];
    }

    /**
     * get user orders
     */
    public function orders(Request $request){

        $validator = Validator::make($request->all(),[
            'page' => 'nullable|integer',
            'type' =>['nullable','regex:/^([0-9]+|all)$/'],
        ]);

        if($validator->fails()){
            return [
                'success' => false,
                'message' => $validator->messages()->first(),
                'errors'=> $validator->errors()->toArray()
            ];
        }


        $query = Order::where('user_id', Auth::id())->orderBy('created_at', 'desc');

        if($request->dt_condition){
            $dt_condition = Carbon::now()->subDays($request->dt_condition)->toDateString();
            $query->whereDate('created_at', '>=', $dt_condition);
        }
        
        if(($request->order_type || $request->order_type === 0 || $request->order_type==='0') && $request->order_type != 'all'){
            $query->where('type', $request->order_type); // 
        }

        return [
            'success'=>true,
            'message'=>new OrderCollection ($query->paginate(10)),
/*             'sql'=>$query->toSql(),
            'bindings'=>$query->getBindings(),
            'condition'=>($request->order_type || $request->order_type === 0 || $request->order_type==='0') && $request->order_type != 'all' */
        ];
    }

    /**
     * get projects
     */
    public function projects(Request $request){

        /* $validator = Validator::make($request->all(),[
            'page' => 'nullable|integer',
            'type' =>['nullable','regex:/^([0-9]+|all)$/'],
        ]);

        if($validator->fails()){
            return [
                'success' => false,
                'message' => $validator->messages()->first(),
                'errors'=> $validator->errors()->toArray()
            ];
        } */

        $query = Project::get();

        return [
            'success'=>true,
            'message'=>$query,
/*             'sql'=>$query->toSql(),
            'bindings'=>$query->getBindings(),
            'condition'=>($request->order_type || $request->order_type === 0 || $request->order_type==='0') && $request->order_type != 'all' */
        ];
    }

     /**
     * get products
     */
    public function products(Request $request){

        $validator = Validator::make($request->all(),[
            'page' => 'nullable|integer',
            'type' =>['nullable','regex:/^([0-9]+|all)$/'],
        ]);

        if($validator->fails()){
            return [
                'success' => false,
                'message' => $validator->messages()->first(),
                'errors'=> $validator->errors()->toArray()
            ];
        }

        $query = Product::get();
        //'message'=>new OrderCollection ($query->paginate(10)),
        return [
            'success'=>true,
            'message'=>new ProductCollection ($query), // new ProductCollection ($query)
/*             'sql'=>$query->toSql(),
            'bindings'=>$query->getBindings(),
            'condition'=>($request->order_type || $request->order_type === 0 || $request->order_type==='0') && $request->order_type != 'all' */
        ];
    }

     /**
     * get stores
     */
    public function stores(Request $request){

        $validator = Validator::make($request->all(),[
            'page' => 'nullable|integer',
            'type' =>['nullable','regex:/^([0-9]+|all)$/'],
        ]);

        if($validator->fails()){
            return [
                'success' => false,
                'message' => $validator->messages()->first(),
                'errors'=> $validator->errors()->toArray()
            ];
        }

        $query = Store::get();
        //'message'=>new OrderCollection ($query->paginate(10)),
        return [
            'success'=>true,
            'message'=>new StoreCollection ($query), // new ProductCollection ($query)
/*             'sql'=>$query->toSql(),
            'bindings'=>$query->getBindings(),
            'condition'=>($request->order_type || $request->order_type === 0 || $request->order_type==='0') && $request->order_type != 'all' */
        ];
    }

    /**
     * get albums
     */
    public function albums(Request $request){

        $validator = Validator::make($request->all(),[
            'page' => 'nullable|integer',
            'type' =>['nullable','regex:/^([0-9]+|all)$/'],
        ]);

        if($validator->fails()){
            return [
                'success' => false,
                'message' => $validator->messages()->first(),
                'errors'=> $validator->errors()->toArray()
            ];
        }

        $query = Album::where('user_id', Auth::id())->get();
        //'message'=>new OrderCollection ($query->paginate(10)),
        return [
            'success'=>true,
            'message'=>new AlbumCollection ($query), // new ProductCollection ($query)
        ];
    }

    public function product_solutions(Request $request){

        $validator = Validator::make($request->all(),[
            'page' => 'nullable|integer',
            'type' =>['nullable','regex:/^([0-9]+|all)$/'],
        ]);

        if($validator->fails()){
            return [
                'success' => false,
                'message' => $validator->messages()->first(),
                'errors'=> $validator->errors()->toArray()
            ];
        }


        $query = Product_solution::where('product_id', $request->productID);

        return [
            'success'=>true,
            'message'=>new ProductSolutionCollection ($query->paginate(10)),
        ];
    }

    public function get2Dpics(){

        $videos = Media::where('type', 1)->where('status', 0)->whereNotNull('original')->where('is_staff_uploaded',0)->get();
        $pics = [];
        foreach($videos as $video){
            $pics[] = (object)['id'=>$video->id,
            'name'=>$video->name,
            'obj'=>Storage::disk('s3')->temporaryUrl($video->original??$video->obj, now()->addHour()),
            'path'=> (new OrderRepository($video->order))->getPath($video->id)];
        }
        return [
            'success'=>true,
            'message'=>$pics,
        ];
    }

    public function mediaChangeStatus($media){
        $repo = new OrderRepository($media->order);
            $media->status = 1;
            if($media->type == 1){
                $media->obj = $repo->getPath($media->id);
            }
            if($media->type ==0){
                $media->obj = $repo->getVideoPath($media->id);
                //if media is created by staff, add cover
                if($media->is_staff_uploaded == 1){
                    $media->cover = $repo->getVideoCoverPath($media->id);
                }
            }
            $media->finish_time=now();
            $media->save();
            if($media->type == 0){
                event(new CompleteTransformVideo($media));
            }
            if($media->type == 1){
                event(new CompleteTransformPic($media));
            }
    }

    public function set2DpicFinish(Request $request){

        $validator = Validator::make($request->all(),[
            'id' => 'required|exists:media,id',
        ]);

        if($validator->fails()){
            return [
                'success' => false,
                'message' => '查無此媒體，請檢查輸入資料',
                'errors'=> $validator->errors()->toArray()
            ];
        }

        $media = Media::where('id', $request->id)->first();
        if($media){
            $this->mediaChangeStatus($media);
        }
        return [
            'success'=>true
        ];
    }

    public function deleteVideo(Request $request, Media $media){

        if ($request->user()->cannot('update', $media)) {
            abort(403);
        }

        $media->status = 3;
        //delete original file
        if($media->original && Storage::disk('s3')->exists($media->original)){
            Storage::disk('s3')->delete($media->original);
        }
        //delete obj file
        if($media->obj && Storage::disk('s3')->exists($media->obj)){
            Storage::disk('s3')->delete($media->obj);
        }
        //delete cover file
        if($media->cover && Storage::disk('s3')->exists($media->cover)){
            Storage::disk('s3')->delete($media->cover);
        }

        $media->original = null;
        $media->obj = null;
        $media->cover = null;

        $media->save();
        return [
            'success'=>true
        ];
    }

    public function videoFailed(Request $request, Media $media){

        if ($request->user()->cannot('update', $media)) {
            abort(403);
        }

        $media->status = 2;
        $media->save();
        event(new PicUploadFailed($media));
        return [
            'success'=>true
        ];
    }

    public function getVideos(){
        $media = Media::where('type', 0)->where('status', 0)->whereNotNull('original')->where('is_staff_uploaded',0)->get();
        $videos = [];
        foreach($media as $medium){
            $videos[] = (object)['id'=>$medium->id,
            'name'=>$medium->name,
            'original'=>Storage::disk('s3')->temporaryUrl($medium->original, now()->addHour()),
            'path'=> (new OrderRepository($medium->order))->getVideoPath($medium->id)];
        }
        return [
            'success'=>true,
            'message'=>$videos,
        ];
    }

    public function setVideoFinish(Request $request){
        $validator = Validator::make($request->all(),[
            'id' => 'required|exists:media,id',
        ]);

        if($validator->fails()){
            return [
                'success' => false,
                'message' => '查無此媒體，請檢查輸入資料',
                'errors'=> $validator->errors()->toArray()
            ];
        }

        $media = Media::where('id', $request->id)->first();
        if($media){
            $this->mediaChangeStatus($media);
        }
        return [
            'success'=>true
        ];
    }

    public function video(Request $request, Media $media){
        try{
            $userOrders = Order::where('user_id', $request->user()->id)->where('type', '1')->get();

            $media_arr = [];
            $result_arr = [];
            $albumCollection = null;
            $tmp_media = null;

            $can_view = false;
            
            if($request->user()->cannot('view', $media)){
                foreach ($userOrders as $order) {
                    if($order->product_solution_order == null){
                        continue;
                    }

                    
                    if($order->product_solution_order->product_solution->product->type == 0){
                        $tmp_media = [$order->product_solution_order->product_solution->product->media];
                    }
                    else{
                        $album = collect([$order->product_solution_order->product_solution->product->album]);
                        $albumCollection = new AlbumCollection($album);
                        $tmp_media = $albumCollection->first()->media;
                    }
                    
                    array_push($media_arr, $tmp_media);
                }
                
                /* if($order->id == 8547){
                    return [
                        'success'=>true,
                        'message'=>[
                            'cover'=>$userOrders,
                        ],
                    ];
                } */
        
                /* if($tmp_media==null){
                    return [
                        'success'=>true,
                        'message'=>[
                            'cover'=>$albumCollection,
                        ],
                    ];
                } */
                
                foreach ($media_arr as $temp_arr) {
                    foreach ($temp_arr as $temp) {
                        if ($temp->id == $media->id) {
                            $can_view = true;
                        }

                        if($media_arr){
                            return [
                                'success'=>true,
                                'message'=>[
                                    'cover'=>$media_arr,
                                ],
                            ];
                        }
                    }
                }

                if(!$can_view){
                    abort(403);
                }
            }

            return [
                'success'=>true,
                'message'=>[
                    'cover'=>Storage::disk('s3')->temporaryUrl($media->cover, now()->addHour()),
                    'obj'=>Storage::disk('s3')->temporaryUrl($media->obj, now()->addHour()),
                ],
            ];
        }
        catch(Exception $e){
            return var_dump($e);
        }
    }

    public function test(){
        return [
            'success'=>true,
            'message'=>[
                'mess'=>"oof",
            ],
        ];
    }

    /**
     * get projects
     */
    public function checkPaymentFlow(Request $request){
        // check programming error
        /* $validator = Validator::make($request->all(),[
            'value' => 'nullable', //|integer
            'type' =>['nullable','regex:/^([0-9]+|all)$/'],
        ]);

        if($validator->fails()){
            return [
                'success' => false,
                'message' => $validator->messages()->first(),
                'errors'=> $validator->errors()->toArray()
            ];
        } */

        $user = Auth::user();

        $target = 'points';
       
        if($request->to){
            $target = $request->to;
        }

        // check action type
        switch ($request->type) {
            // check 2to3 available
            case 0:
                //create new order, media and store file to storage
                if($user->$target<-(int)$request->value){
                    return [
                        'success'=>false,
                        'message'=>[
                            'type'=> 'not enough points. Please add value !',
                        ]
                    ];
                }
            
            // check buy items
            case 1:
                $repository = new OrderRepository();
                $repository->createOrder($request);

                if($user->$target<-(int)$request->value){
                    return [
                        'success'=>false,
                        'message'=>[
                            'type'=> 'not enough points to buy. Please add value !',
                        ]
                    ];
                }

                // after trasaction succ
                $repository->userAddValueSucc($request);
                break;

            // check Paypal payment success 
            case 2:
                $repository = new OrderRepository();
                $repository->createOrder($request);
                // ====== check paypal trasaction status =======
                


                // ====================

                // after trasaction succ
                $repository->userAddValueSucc($request);

                if(!$request->from){
                    break;
                }

                if($request->from == 'ads'){
                    $user->ads_times += 1;
                }
                
                break;
            
            case 3:
                

                
                break;
            
            default:
                return [
                    'success'=>false,
                    'message'=>[
                        'type'=> 'ain\'t regular type',
                    ]
                ];
        }

        // if succ, then add value
        $user->$target+=(int)$request->value;
        $user->save();

        return [
            'success'=>true,
            'message'=>[
                'points'=> $user->points,
                'free_points'=> $user->free_points,
            ]
/*           'sql'=>$query->toSql(),
            'bindings'=>$query->getBindings(),
            'condition'=>($request->order_type || $request->order_type === 0 || $request->order_type==='0') && $request->order_type != 'all' */
        ];
    }


    /**
     * create album
     */
    public function albumCreate(Request $request){

        $validator = Validator::make($request->all(),[
            'name' => 'required',
        ]);

        if($validator->fails()){
            return [
                'success' => false,
                'message' => __('register.failed'),
                'errors'=> $validator->errors()->toArray()
            ];
        }

        $user = Auth::user();

        $album = Album::create([
            'name' => $request->name,
        ]);

        $album->user()->associate($user);
        $album->save();

        return [
            'success' => true,
            'message' => [
                'id' =>  $album->id,
                'name' =>  $album->name,
            ]
        ];
    }

    /**
     * add media to album
     */
    public function editToAlbum(Request $request){

        /* $validator = Validator::make($request->all(),[
            'name' => 'required',
        ]);

        if($validator->fails()){
            return [
                'success' => false,
                'message' => __('register.failed'),
                'errors'=> $validator->errors()->toArray()
            ];
        } */
        $albumID = $request->album['id'];
        $productsWithoutAlbum = !Product::whereHas('album', function ($query) use ($albumID) {
            $query->where('album_id', $albumID);
        })->exists();

        if (!$productsWithoutAlbum) {
            return [
                'success' => false,
                'message' => 'your album is in product. You can\'t edit it.'
            ];
    
        }

        $album = Album::where('id', $request->album['id'])->first();
        $request_media = $request->chosenMedia;

        if($request->editType == 'add'){
            foreach ($request->chosenMedia as $key => $value) {
                $media = Media::where('id', $value['id'])->first();
                $media->album()->associate($album);
                $media->save();
            }    
        }
        else if($request->editType == 'delete'){
            foreach ($request->chosenMedia as $key => $value) {
                $media = Media::where('id', $value['id'])->first();
                $media->album_id = null;
                $media->save();
            }    
        }
        
        return [
            'success' => true,
            'message' => [
                'id' =>  $media,
            ]
        ];
    }
    
    /**
     * check if subscribed 
     */
    public function check_product_subscribe(Request $request){
        // check programming error
        $validator = Validator::make($request->all(),[
            'product' => 'nullable', // |integer
            'type' =>['nullable','regex:/^([0-9]+|all)$/'],
        ]);

        if($validator->fails()){
            return [
                'success' => false,
                'message' => $validator->messages()->first(),
                'errors'=> $validator->errors()->toArray()
            ];
        }

        $user = Auth::user();
        
        // $have_order = Product_solution_order::where('email', 'example@example.com')->exists();
        $product = Product::where('id', $request->product['id'])->first();

        $product_solution = $product->product_solution;

        // Prevent subscribe self.
        if($product->type == 0){
            $media_list = [$product->media];
        }
        else{
            $album = collect([$product->album]);
            $albumCollection = new AlbumCollection($album);
            $media_list = $albumCollection->first()->media;
        }

        foreach($media_list as $media){
            if($media->order->user->id == $user->id){
                return [
                    'success'=>false,
                    'message'=>'這是您所上傳的照片',
                ];
            }
        }

        // Prevent subscribe same product.
        foreach($product_solution as $solution){
            $product_solution_orders = $solution->product_solution_order;

            foreach($product_solution_orders as $product_solution_order){
                if($product_solution_order->order->user->id == $user->id && $product_solution_order->status == 0){
                    return [
                        'success'=>false,
                        'message'=>'您已經訂閱過了',
                    ];
                }
            }
        }

        return [
            'success'=>true,
            'message'=>"可以訂閱"
        ];
    }

    /**
     * subscribe a product
     */
    public function product_subscribe(Request $request){
        // check programming error
        $validator = Validator::make($request->all(),[
            'product_solution' => 'nullable', // |integer
            'type' =>['nullable','regex:/^([0-9]+|all)$/'],
        ]);

        if($validator->fails()){
            return [
                'success' => false,
                'message' => $validator->messages()->first(),
                'errors'=> $validator->errors()->toArray()
            ];
        }

        $user = Auth::user();
        
        // $have_order = Product_solution_order::where('email', 'example@example.com')->exists();
        $product_solution = Product_solution::where('id', $request->product_solution['id'])->first();
        $product = $product_solution->product;
        $product_solution_orders = $product_solution->product_solution_order;

        // Prevent subscribe self.
        if($product->type == 0){
            $media_list = [$product->media];
        }
        else{
            $album = collect([$product->album]);
            $albumCollection = new AlbumCollection($album);
            $media_list = $albumCollection->first()->media;
        }

        foreach($media_list as $media){
            if($media->order->user->id == $user->id){
                return [
                    'success'=>false,
                    'message'=>'這是您所上傳的照片',
                ];
            }
        }

        // Prevent subscribe same product.
        foreach($product_solution_orders as $product_solution_order){
            if($product_solution_order->order->user->id == $user->id && $product_solution_order->status == 0){
                return [
                    'success'=>false,
                    'message'=>'您已經訂閱過了',
                ];
            }
        }

        // if succ, then make an order and make a solution order
        $repository = new OrderRepository();
        $order_detail = $repository->subscribeProduct($request);

        $media = $repository->getMedia();

        event(new PicUploaded($media));
        // ====== check paypal trasaction status =======

        return [
            'success'=>true,
            'message'=>[
                'order'=> $order_detail,
            ]
        ];
    }

    /**
     * unsubscribe a product
     */
    public function product_unsubscribe(Request $request){
        // check programming error
        $validator = Validator::make($request->all(),[
            'order' => 'nullable', // |integer
            'type' =>['nullable','regex:/^([0-9]+|all)$/'],
        ]);

        if($validator->fails()){
            return [
                'success' => false,
                'message' => $validator->messages()->first(),
                'errors'=> $validator->errors()->toArray()
            ];
        }

        $user = Auth::user();

        // $have_order = Product_solution_order::where('email', 'example@example.com')->exists();

        // Cancel mark when order variable is an Order object
        /* $order = Order::where('id', $request->order['id']=)->first(); */

        $order = Order::where('id', $request->order)->first();
        $product_solution_order = $order->product_solution_order;

        if(!$order->product_solution_order){
            return [
                'success'=>false,
                'message'=>'沒有這筆訂單',
            ];
        }

        $product_solution_order->status = 1;
        $product_solution_order->save();

        return [
            'success'=>true,
            'message'=>'取消訂閱成功'
        ];
    }
}
