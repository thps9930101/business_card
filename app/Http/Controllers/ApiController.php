<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Media;
use App\Models\Order;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Notifications\ConfirmUserCode;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;

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
                'message' => '登入失敗，查無用戶資訊',
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
                'message' => '登入失敗，查無用戶資訊',
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
            'password' => 'required|confirmed'
        ]);

        if($validator->failed()){
            return [
                'success' => false,
                'message' => '註冊失敗，請檢查輸入資料',
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
            $user->notify(new ConfirmUserCode($code));
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
            'message' => '認證信已重新發送！'
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
                        'message'=>'忘記密碼電子郵件發送成功！'
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
                'password' => 'nullable|password|confirmed',
                'old_password'=>'nullable|required_with:password|current_password'
            ]);

            if($validator->failed()){
                return [
                    'success' => false,
                    'message' => '更新用戶資料失敗，請檢查輸入資料',
                    'errors'=> $validator->errors()->toArray()
                ];
            }

            $user = Auth::user();
            $user->name = $request->name;
            $user->phone = $request->phone;
            $user->password = Hash::make($request->password);
            $user->save();

            return [
                'success'=>true,
                'message'=>'update user success!'
            ];
    }

    /**
     * update member name
     */
    public function updateMemberName(Request $request){

        $validator = Validator::make($request->all(),[
            'name' => 'required',
        ]);

        if($validator->failed()){
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

        if($validator->failed()){
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

        $orders =Order::where('user_id', Auth::id())->get();
        return [
            'success'=>true,
            'message'=>[
                'orders'=>$orders,
                'count'=> $orders->count()
            ],
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

}
