<?php

namespace App\Http\Controllers;

use Recaptcha;
use App\Models\User;
use App\Models\Media;
use App\Models\Order;
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
use Illuminate\Support\Facades\Storage;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;


class ApiController extends Controller
{


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

                $validator = Validator::make($request->all(),[
                    'video' => 'required|mimes:mp4,mov,ogg,qt|max:1048576',
                ]);

                if($validator->fails()){
                    return [
                        'success' => false,
                        'message' => '上傳影片失敗，請檢查輸入資料',
                        'errors'=> $validator->errors()->toArray()
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
                ];

    }

    /**
     * upload picture
     */
    public function uploadPicture(Request $request){

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
            //create new order, media and store file to storage
            $repository = new OrderRepository();
            $repository->userUploadMedia($request);

            $media = $repository->getMedia();

            event(new PicUploaded($media));


            return [
                'success'=>true,
                'message'=>'upload picture success!',
            ];
    }

    /**
     * upload canvas picture
     */
    public function uploadCanvas(Request $request){

        $validator = Validator::make($request->all(),[
            'pic' => 'required|string',
        ]);

        if($validator->fails()){
            return [
                'success' => false,
                'message' => '上傳圖片失敗，請檢查輸入資料',
                'errors'=> $validator->errors()->toArray()
            ];
        }
        //create new order, media and store file to storage
        $repository = new OrderRepository();
        $repository->userUploadMediaFromCanvas($request);

        $media = $repository->getMedia();

        event(new PicUploaded($media));

        return [
            'success'=>true,
            'message'=>'upload picture success! media id: '.$media->id,
        ];
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

        $query = Order::where('user_id', Auth::id());

        if(($request->order_type || $request->order_type === 0 || $request->order_type==='0') && $request->order_type != 'all'){
            $query->where('type', $request->order_type);
        }

        return [
            'success'=>true,
            'message'=>new OrderCollection ($query->paginate(10)),
/*             'sql'=>$query->toSql(),
            'bindings'=>$query->getBindings(),
            'condition'=>($request->order_type || $request->order_type === 0 || $request->order_type==='0') && $request->order_type != 'all' */
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

    public function video(Request $request,Media $media){

        if ($request->user()->cannot('view', $media)) {
            abort(403);
        }

        return [
            'success'=>true,
            'message'=>[
                'cover'=>Storage::disk('s3')->temporaryUrl($media->cover, now()->addHour()),
                'obj'=>Storage::disk('s3')->temporaryUrl($media->obj, now()->addHour()),
            ],
        ];
    }

}
