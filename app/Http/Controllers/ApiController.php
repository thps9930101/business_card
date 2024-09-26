<?php

namespace App\Http\Controllers;

use Recaptcha;
use Carbon\Carbon;
use App\Models\User;
use App\Models\cards;
use App\Models\materials;
use App\Models\payments;
use App\Models\models;
use App\Models\companies;
use App\Models\companies_user;
use App\Models\Media;
use App\Models\Order;
use App\Models\Store;
use App\Models\Album;
use App\Models\AlbumDetail;
use App\Models\Project;
use App\Models\Product;
use App\Models\Payment;
use App\Models\price_menu;
use App\Models\Notification;
use App\Models\Plan_solution;
use App\Models\Plan_solution_order;
use App\Models\Product_solution;
use App\Models\Product_solution_order;
use App\Models\Key;

// use App\Models\TimesOrder;
use App\Events\PicUploaded;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Events\PicUploadFailed;
use App\Repository\OrderRepository;
use App\Events\CompleteTransformPic;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\UploadedFile;
use App\Events\CompleteTransformVideo;
use App\Events\AIBoxRefresh;
use App\Notifications\ConfirmUserCode;
use App\Notifications\ClickTimesRemind;
use App\Notifications\NoTimesRemind;
use App\Notifications\SolutionExpiredNotify;
use App\Notifications\ResetPasswordLink;
use App\Http\Resources\MediaCollection;
use App\Http\Resources\OrderCollection;
use App\Http\Resources\AlbumCollection;
use App\Http\Resources\StoreCollection;
use App\Http\Resources\ProductCollection;
use App\Http\Resources\ProductSolutionCollection;
use App\Jobs\AutoDeleteGuestMedia;
use App\Jobs\ProductUnsubscribe;
use App\Jobs\ResetPasswordTokenExpired;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Encryption\Encrypter;

use Illuminate\Support\Facades\Validator;
// use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Password;
use Intervention\Image\Facades\Image;

use ZipArchive;

class ApiController extends Controller
{
    public function get_cpu_usage() {
        exec('top -b -n 1 | grep "Cpu(s)"', $output);
        $cpuInfo = explode(",", $output[0]);
        $cpuUsage = trim(str_replace("Cpu(s):", "", $cpuInfo[0]));

        return $cpuUsage;
    }

    /**
     * login
     */
    public function login(Request $request) {
        
        $validator = Validator::make($request->all(),[
            'account' => 'required',
            'password' => 'required'
        ]);
        
        
        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => __('auth.failed'),
                'errors' => $validator->errors()->toArray()
            ];
        }

        $credentials = $request->only('account', 'password');

        if ($result = Auth::attempt($credentials)) {
            $auth = Auth::user();
            $token = $auth->createToken($request->account)->plainTextToken;

            $user = User::Where('id', $auth->id)->first();
            $user->remember_token = $token;
            $user->save();

            return [
                'success' => true,
                'message' => [
                    'id' =>  $auth->id,
                    'name' =>  $auth->name,
                    'email' =>  $auth->email,
                    'token'=>  $token
                ]
            ];
        } else {
            return [
                'success' => false,
                'message' => __('auth.failed'),
                'errors' => $result
            ];
        }
    }

    public function company_login(Request $request) {
        
        $validator = Validator::make($request->all(),[
            'account' => 'required',
            'password' => 'required'
        ]);
        
        
        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => __('auth.failed'),
                'errors' => $validator->errors()->toArray()
            ];
        }

        $credentials = $request->only('account', 'password');
        $company = companies::where('account', $request->account)->first();
            
        if(!$company)
        {
            return [
                'success' => false,
                'message' => __('auth.failed'),
                'errors' => $validator->errors()->toArray()
            ];
        }

        $token = Str::random(60);

        while (companies::where('token', $token)->exists()) {
            $token = Str::random(60);
        }

        $company->token = $token;
        $company->save();

        if ($company) {
            if($company->password != $request->password)
            {
                return [
                    'success' => false,
                    'message' => __('auth.failed'),
                    'errors' => $validator->errors()->toArray()
                ];
            }
            return [
                'success' => true,
                'message' => [
                    'id' =>  $company->id,
                    'name' =>  $company->name,
                    'account' =>  $company->account,
                    'level' => $company->level,
                    'token' =>  $company->token,
                ]
            ];
        } else {
            return [
                'success' => false,
                'message' => __('auth.failed'),
                'errors' => $company
            ];
        }
    }
    

    public function testBC(Request $request) {
        $getAllBC_Result = [
            'bearerToken_requery' => true,
            'input_param' => [
                
            ],
            "success" => [
                'success' => true,
                'message' => [
                    [
                        "id" => 1,
                        "name" => "bc_1",
                        "releaseName" => "Joe's Buysiness Card",
                        "update_at" => "2023/12/05 12:00:09",
                        "create_at" => "2023/12/04 12:00:09",
                        "downloadTimes" => 15
                    ],
                    [
                        "id" => 2,
                        "name" => "bc_2",
                        "releaseName" => "Joe's Buysiness Card 2",
                        "update_at" => "2023/12/05 13:00:29",
                        "create_at" => "2023/12/04 12:10:59",
                        "downloadTimes" => 7
                    ],
                ]
            ],
            "failed" => [
                'success' => false,
                'message' => "getAllBC error"
            ],
        ];

        $addBC_Result = [
            'bearerToken_requery' => true,
            'input_param' => [
                
            ],
            "success" => [
                'success' => true,
                'message' => [
                    "id" => 1
                ]
            ],
            "failed" => [
                'success' => false,
                'message' => "addBC error"
            ],
        ];

        $editBC_Result = [
            'bearerToken_requery' => true,
            'input_param' => [
                'id' => 'bc_id',
                'card' => [
                    "ig" => "...",
                    "twitter/x" => "...",
                    "line" => "...",
                    "fb" => "...",
                    "card_front" => "pic_id",
                    "card_back" => "pic_id",
                    "model" => "model_id"
                ]
            ],
            "success" => [
                'success' => true,
                'message' => "change success"
            ],
            "failed" => [
                'success' => false,
                'message' => "editBC error"
            ],
        ];

        $getMaterial_Result = [
            'bearerToken_requery' => true,
            'input_param' => [
            ],
            "success" => [
                'success' => true,
                'message' => [
                    "modelList" => [
                        [
                            "id" => "1",
                            "texture" => "url",
                            "mesh" => "url",
                        ],
                        [
                            "id" => "2",
                            "texture" => "url",
                            "mesh" => "url",
                        ],
                        [
                            "id" => "3",
                            "texture" => "url",
                            "mesh" => "url",
                        ],
                    ],
                    "cardPicList" => [
                        [
                            "id" => "4",
                            "url" => "url",
                        ],
                        [
                            "id" => "5",
                            "url" => "url",
                        ],
                        [
                            "id" => "6",
                            "url" => "url",
                        ],
                    ]
                ]
            ],
            "failed" => [
                'success' => false,
                'message' => "getMaterial error"
            ],
        ];

        $getBC_Result = [
            'bearerToken_requery' => false,
            'input_param' => [
                'id' => 'bc_id'
            ],
            "success" => [
                'success' => true,
                'message' => [
                    "ig" => "...",
                    "twitter/x" => "...",
                    "line" => "...",
                    "fb" => "...",
                    "card_front" => "url",
                    "card_back" => "url",
                    "model" => [
                        "texture" => "url",
                        "mesh" => "url"
                    ]
                ]
            ],
            "failed" => [
                'success' => false,
                'message' => "getBC error"
            ],
        ];

        return [
            'message' => [
                "getAllBC_Result" => $getAllBC_Result,
                "addBC_Result" => $addBC_Result,
                "editBC_Result" => $editBC_Result,
                "getMaterial_Result" => $getMaterial_Result,
                "getBC_Result" => $getBC_Result,
            ]
        ];
    }

    /**
     * register
     */
    public function register(Request $request) {

        $validator = Validator::make($request->all(),[
            'account' => 'required',
            'email' => 'required',
            'name' => 'required',
            'password' => 'required',
        ]);

        if($validator->fails()){
            return [
                'success' => false,
                'message' => [
                    "isExist" => false,
                    "error" => __('register.failed')
                ],
            ];
            
        }

        if (User::where('account', $request->account)->exists())
        {
            return [
                'success' => false,
                'message' => [
                    "isExist" => true,
                    "id" => User::where('account', $request->account)->first()->id,
                    "token" => User::where('account', $request->account)->first()->remember_token,
                    "error" => "帳號已存在，無法註冊"
                ],
            ];
        }
    
        $user = User::create([
            'account' => $request->account,
            'email' => $request->email,
            'name' => $request->name,
            'password' => Hash::make($request->password),
            'download_time' => 0
        ]);

        if($user){
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
                'account' =>  $user->account,
                'email' =>  $user->email,
                'token'=>  $user->createToken($request->email)->plainTextToken,
                'confirm_url'=>  route('registerMember', ['code'=>$user->confirm_code])]
        ];

    }

    public function errorReport(Request $request){
        $validator = Validator::make($request->all(),[
            'error' => 'required'
        ]);  
        Log::info($request->error);
    }

    public function company_register(Request $request){
        $validator = Validator::make($request->all(),[
            'name' => 'required',
            'account' => 'required',
            'password' => 'required|confirmed',
        ]);

        $res = companies::where('account', $request->account)->get();

        if ($res->count() > 0) {
            return [
                'success' => false,
                'message' => "this account already exist !"
            ];
        }

        $company = new companies();

        $company->account = $request->account;
        $company->name = $request->name;
        $company->password = $request->password;

        $company->save();
        return [
            'success' => true,
            'message' => [
                'account' =>  $company->account,
                'name' =>  $company->name,
            ]
        ];
    }

    /**
     * sendConfirmEmail
     */
    public function sendConfirmEmail(Request $request) {

        $user = Auth::user();

        if ($user->confirm_code_expired_at < now()) {
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
    public function forgetPassword(Request $request) {

        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        // $status = Password::sendResetLink(
        //     $request->only('email')
        // );

        // 紀錄至資料庫
        $user = User::Where('email', $request->email)->first();
        
        if (!$user)
        {
            return [
                'success' => false,
                'message' => 'UserNotFound', 
                'error' => __($status)
            ];
        }

        $resetPasswordToken = Str::random(60);
        $user->resetPasswordToken = $resetPasswordToken;
        $user->save();
        
        // 紀錄5分鐘Token失效
        // ResetPasswordTokenExpired::dispatch($user->id)->delay(now()->addMinutes(1));
        

        // 將連結寄給使用者
        try {
            $user->notify(new ResetPasswordLink($resetPasswordToken, $user->email));
        }
        catch(e) {
            return [
                'success' => false,
                'message' => 'CanNotSendMail', 
                'error' => __($status)
            ];
        }


        return [
            'success' => true,
            'message' => "sendResetLink"
        ];
        // return $status == Password::RESET_LINK_SENT
        //             ? [
        //                 'success' => true,
        //                 'message' => __('passwords.sent')
        //             ]
        //             : [
        //                 'success' => false,
        //                 'message' => 'emailSendingFailed', 
        //                 'error' => __($status)
        //             ];

    }

    public function companies_UpdatePassword(Request $request) {

        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'Password' =>'required',
            'newPassword' => 'required',
        ]);

        if (!$request->token)
            abort(415);

        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => __('register.failed'),
                'errors' => $validator->errors()->toArray()
            ];
        }

        $resetPasswordToken = $request->token;
        $password = $request->Passowrd;
        $companies = companies::where('token', $resetPasswordToken)->first();

        if ($companies) {
            if($companies->passowrd != $password)
            {
                return [
                    'success' => false,
                    'message' => "passowrd Error"
                ];
            }

            //$companies->password = Hash::make($request->newPassword);
            $companies->password = $request->newPassword;
            $companies->save();

            return [
                'success' => true,
                'message' => "ChangeSuccess"
            ];   

        }
        else {
            abort(415);
            return [
                'success' => false,
                'message' => "UserNotFound"
            ];
        }

        
    }

    /**
     *  api
     */
    public function getUserData(Request $request){
        
        $validator = Validator::make($request->all(),[
            'email' => 'required'
        ]);

        if($validator->fails()){
            return [
                'success' => false,
                'message' => __('register.failed'),
                'errors'=> $validator->errors()->toArray()
            ];
        }

        $user = User::where('email',$request->email);

        if($user->exists())
        {
            $user = $user->first();
            return [
                'success' => true,
                'message' => [       
                    'name' => $user->name,       
                    'email' => $user->email,
                    'download_time' => $user->download_time,
                    'bonus_times' => $user->bonus_times                      
                ]
            ];
        }else{
            return [
                'success' => false,
                'message' => "找不到此帳號"
            ];
        }
    }

    public function changeFrontBack(Request $request){
        $validator = Validator::make($request->all(),[
            'token' => 'required',
            'user_id' => 'required',
            'card_id' => 'required'
        ]);

        if (!$request->token)
            abort(415);

        if($validator->fails()){
            return [
                'success' => false,
                'message' => __('register.failed'),
                'errors'=> $validator->errors()->toArray()
            ];
        }

        $company = companies::where('token', $request->token)->first();
        
        if(!$company)
        {
            abort(415);
            return [
                'success' => false,
                'message' => "請重新登入"
            ];
        }

        // $company_user = companies_user::where('company_id', $company->id)->where('user_id', $request->user_id)->first();

        // if($company->level != 0)
        // {
        //     if(!$company_user)
        //     {
        //         return [
        //             'success' => false,
        //             'message' => "找不到此用戶"
        //         ];
        //     }
        // }

        // if($company->level != 0)
        // {
        //     if($company_user->company_id != $company_id)
        //     {
        //         return [
        //             'success' => false,
        //             'message' => "無法編輯此用戶"
        //         ];
        //     }
        // }

        $card = cards::where('id', $request->card_id)->first();

        if(!$card)
        {
            return [
                'success' => false,
                'message' => "找不到此卡片"
            ];
        }

        $front = $card->card_front_id;
        $back = $card->card_back_id;

        $card->version = $card->version + 1; 
        $card->card_front_id = $back;
        $card->card_back_id = $front;
        $card->save();

        return [
            'success' => true,
            'message' => "編輯成功"
        ];
    }

    public function testEnc(Request $request){
        $file = $request->file('file');
        $contents = file_get_contents($file->getRealPath());

        $keyLength = 32; // AES-256 需要 32 字節的金鑰
        // 生成隨機的字串作為金鑰
        $fileNameWithoutExtension = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

        // 创建临时文件来存储压缩的 ZIP 文件
        $zipFileName = $fileNameWithoutExtension . '.zip';
        $zipFilePath = tempnam(sys_get_temp_dir(), $zipFileName);

       $zip = new ZipArchive;
       if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
           $zip->addFile($file->getRealPath(), $file->getClientOriginalName());
           $zip->close();

       } else {
            return response()->json([
                'success' => false,
                'message' => 'Unable to create ZIP file',
            ], 500);
       }
       $zipContents = file_get_contents($zipFilePath);

        // 加密秘钥和偏移向量
        $key = "12345678988822221234567898882222";  // 加密秘钥，这个key的字符位数要求：4的倍数
        $iv = '8NONwyJtHesysWpM';  // 向量（偏移向量）

        // 加密ZIP文件数据
        $encryptedZipData = openssl_encrypt($zipContents, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);

        // 创建一个临时文件来存储加密后的ZIP数据
        $encryptedTempFilePath = tempnam(sys_get_temp_dir(), 'enc');
        file_put_contents($encryptedTempFilePath, $encryptedZipData);

        // 将加密后的ZIP文件保存到存储
        Storage::put('encrypted_images/' . $zipFileName, $encryptedZipData);

        // 上传到S3
        $s3Path = env('APP_ENV') . "/encTest/model/" . uniqid() . '/' . $zipFileName;
        Storage::disk('s3')->put($s3Path, $encryptedZipData);

        return response()->json([
            'success' => true,
            'message' => 'File encrypted successfully',
        ]);

    }

    public function company_CheckLevel(Request $request){
        $validator = Validator::make($request->all(),[
            'token' => 'required'
        ]);

        if (!$request->token)
            abort(415);

        if($validator->fails()){
            return [
                'success' => false,
                'message' => __('register.failed'),
                'errors'=> $validator->errors()->toArray()
            ];
        }

        $company = companies::where('token', $request->token);
        
        if(!$company->exists())
        {
            abort(415);
            return [
                'success' => false,
                'message' => "請重新登入"
            ];
        }

        $company = $company->first();

        if($company->level == 1)
        {
            return [
                'success' => true,
                'message' => true
            ];
        }else{
            return [
                'success' => true,
                'message' => false
            ];  
        }
    }

    public function sendTimesMail(Request $request){

        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);

        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => __('register.failed'),
                'errors' => $validator->errors()->toArray()
            ];
        }
        
        $user = User::where('id',$request->id)->first();

        if ($user->confirm_code_expired_at < now()) {
            $code = Str::random(60);
            $user->confirm_code = $code;
            $user->confirm_code_expired_at = now()->addDays(7);
            $user->save();
        }

        $user->notify(new ClickTimesRemind($user->confirm_code));

        return [
            'success' => true,
            'message' => __('register.resent')
        ];
    }
    /**
     * version
     */
    public function getVersion(Request $request){
        $validator = Validator::make($request->all(), 
        [
            'bc_id' => 'required'
        ]);

        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => __('register.failed'),
                'errors' => $validator->errors()->toArray()
            ];
        }

        $card = cards::where('id',$request->bc_id)->first();
        //TODO 檢查是否存在

        $version_Result = [
            "success" => [
                'success' => true,
                'message' => [
                    'version' => $model->version
                ]
            ]
        ];
        $social_array = [
            'fax' => $card->fax,
            'address' => $card->address,
            'telegram' => $card->telegram,
            'whatsapp' => $card->whatsapp,
            'instagram' => $card->instagram,
            'facebook' => $card->facebook,
            'X' => $card->X,
            'web' => $card->web,
            'line' =>$card->line,
            'name' => $card->name,
            'email' => $card->email,
            'phone' => $card->phone,
            'wechat' => $card->wechat,
            'tiktok' => $card->tiktok
        ];
        
        foreach($social_array as $name => $social)
        {
            if($social != null)
                $version_Result['success']['message'][$name] = $social;
        }
        return [
            $version_Result['success']
        ];
    }

    public function editTimes(Request $request){
        $validator = Validator::make($request->all(),[
            'token' => 'required',
            'user_id' => 'required',
            'times' => 'required'
        ]);
    
        if (!$request->token)
            abort(415);

        if($validator->fails()){
            return [
                'success' => false,
                'message' => __('register.failed'),
                'errors'=> $validator->errors()->toArray()
            ];
        }

        $company = companies::where('token',$request->token)->first();
        
        if(!$company){
            abort(415);
            return[
                'success' => false,
                'message' => "無法取得公司資訊，請重新登入"
            ];
        }

        if($company->level != 0)
        {
            return[
                'success' => false,
                'message' => "權限不足，請通知總公司"
            ];
        }

        $user = User::where('id',$request->user_id)->first();

        if(!$user)
        {
            return[
                'success' => false,
                'message' => "找不到此用戶，請重新刷新頁面"
            ];
        }

        try
        {
            $user->download_time = $request->times;
            $user->save();
        }
        catch(Exception $e){
            return[
                'success' => false,
                'message' => "資料庫發生錯誤"
            ];
        }
        return[
            'success' => true,
            'message' => "編輯次數成功"
        ];
    }

    public function rollback_times(Request $request){
        $validator = Validator::make($request->all(),[
            'token' => 'required',
            'user_id' => 'required',
            'plan_id' => 'required'
        ]);
    
        if (!$request->token)
            abort(415);

        if($validator->fails()){
            return [
                'success' => false,
                'message' => __('register.failed'),
                'errors'=> $validator->errors()->toArray()
            ];
        }

        if (!Auth::user()) {
            return [
                'success' => false,
                'message' => __('register.failed'),
                'errors'=> $validator->errors()->toArray()
            ];
        }

        $company = companies::where("token",$request->token)->first();
        if(!$company)
        {
            abort(415);
            return [
                'success' => false,
                'message' => "error"
            ];
        }

        $user = User::where('id',$request->user_id)->first();
        if(!$user)
        {
            return [
                'success' => false,
                'message' => "error"
            ];
        }

        $company_user = companies_user::where('company_id',$company->id)->where('user_id',$user->id);
        if(!$company_user)
        {
            return [
                'success' => false,
                'message' => "error"
            ];
        }
        
        $company_user->delete();

        $plan = price_menu::where('id',$request->plan_id)->first();
        if(!$plan)
        {
            return [
                'success' => false,
                'message' => "error"
            ];
        }

        $user->download_time = $user->download_time - $plan->times;
        $user->save();
        return [
            'success' => true,
            'message' => ""
        ];
    }

    public function rollback_material(Request $request){
        $validator = Validator::make($request->all(),[
            'id' => 'required'
        ]);
    
        if($validator->fails()){
            return [
                'success' => false,
                'message' => __('register.failed'),
                'errors'=> $validator->errors()->toArray()
            ];
        }
        
        if (!Auth::user()) {
            return [
                'success' => false,
                'message' => __('register.failed'),
                'errors'=> $validator->errors()->toArray()
            ];
        }

        try{
            $material = materials::where('id',$request->id)->first();
            if ($material)
            {
                if ($material->card_url) {
                    if (Storage::disk('s3')->exists($material->card_url))
                        Storage::disk('s3')->delete($material->card_url);
                }  
                $material->delete();
            }
            
            return[
                'success' => true,
                'message' => ""
            ];
        }
        catch(Exception $e){
            return[
                'success' => false,
                'message' => $e
            ];
        }
    }

    public function rollback_model(Request $request){
        $validator = Validator::make($request->all(),[
            'id' => 'required'
        ]);
    
        if($validator->fails()){
            return [
                'success' => false,
                'message' => __('register.failed'),
                'errors'=> $validator->errors()->toArray()
            ];
        }
        
        if (!Auth::user()) {
            return [
                'success' => false,
                'message' => __('register.failed'),
                'errors'=> $validator->errors()->toArray()
            ];
        }

        try{
            $model = models::where('id',$request->id)->first();
            if ($model)
            {
                if ($model->mesh_url) {
                    if (Storage::disk('s3')->exists($model->mesh_url))
                        Storage::disk('s3')->delete($model->mesh_url);
                }

                if ($model->texture_url) {
                    if (Storage::disk('s3')->exists($model->texture_url))
                        Storage::disk('s3')->delete($model->texture_url);
                }

                if ($model->cover_url) {
                    if (Storage::disk('s3')->exists($model->cover_url))
                        Storage::disk('s3')->delete($model->cover_url);
                }

                if ($model->cover_half_url) {
                    if (Storage::disk('s3')->exists($model->cover_half_url))
                        Storage::disk('s3')->delete($model->cover_half_url);
                }
                
                $model->delete();
            }
            
            return[
                'success' => true,
                'message' => ""
            ];
        }
        catch(Exception $e){
            return[
                'success' => false,
                'message' => $e
            ];
        }
    }

    public function rollback_card(Request  $request){
        $validator = Validator::make($request->all(),[
            'id' => 'required'
        ]);

        if (!Auth::user()) {
            return [
                'success' => false,
                'message' => __('register.failed'),
                'errors'=> $validator->errors()->toArray()
            ];
        }

        if ($validator->fails()){
            return [
                'success' => false,
                'message' => __('register.failed'),
                'errors'=> $validator->errors()->toArray()
            ];
        }
        
        $card = cards::where('id',$request->id)->first();
        if($card)
            $card->delete();

        return [
            'success' => true,
            'message' =>""
        ];
    }

    public function addPrice(Request $request){
        $validator = Validator::make($request->all(),[
            'token' => 'required',
            'priceName' => 'required',
            'times' => 'required',
            'price' => 'required',
            'bonus_times' => 'required'
        ]);

        if (!$request->token)
            abort(415);

        if($validator->fails()){
            return [
                'success' => false,
                'message' => __('register.failed'),
                'errors'=> $validator->errors()->toArray()
            ];
        }

        $company = companies::where('token',$request->token)->first();
        if($company)
        {
            if($company->level == 0)
            {
                $price = new price_menu();
                $price->name = $request->priceName;
                $price->times = $request->times;
                $price->price = $request->price;
                $price->bonus_times = $request->bonus_times;
                $price->save();

                return [
                    'success' => true,
                    'message' => "新建成功"
                ];
            }else
            {
                return [
                    'success' => false,
                    'message' => "權限不足，無法進行此操作"
                ]; 
            }
        }else{
            abort(415);
            return [
                'success' => false,
                'message' => "請重新登入"
            ];
        }

    }

    // public function addTimesOrder(Request $request){
    //     $validator = Validator::make($request->all(),[
    //         'token' => 'required',
    //         'mode' => 'required',
    //         'plan_id' => 'required',
    //     ]);
        
    //     $timesOrder = new TimesOrder();
    //     $timesOrder->user_id = $user->id;
    //     $timesOrder->company_id = $company->id;
    //     $timesOrder->save();
    //     return [
    //         'success' => true,
    //         'message' => "加值成功"
    //     ];   
    // }

    // public function getTimesOrder(Request $request){
    //     $validator = Validator::make($request->all(),[
    //         'token' => 'required',
    //     ]);

    //     if (!$request->token)
    //         abort(415);

    //     if($validator->fails()){
    //         return [
    //             'success' => false,
    //             'message' => __('register.failed'),
    //             'errors'=> $validator->errors()->toArray()
    //         ];
    //     }

    //     $company = companies::where('token', $request->token)->first();
    //     if($company)
    //     {
    //         if($company->level == 0)
    //         {
    //             $prices = TimesOrder::all();
    //         }else
    //         {
    //             $prices = TimesOrder::where('company_id',$company->id);
    //             if($prices)
    //                 $prices = $prices->get();
    //         }

    //         $price_array = [];

    //         foreach($prices as $price)
    //         {            
    //             $user = User::where('id',$price->user_id)->first();
    //             $price_company = companies::where('id',$price->company_id)->first();
    //             if($price->mode == 0)
    //             {
    //                 $mode = "新增名片";
    //             }else if($price->mode == 1)
    //             {
    //                 $mode = "加值次數";
    //             }else if($price->mode == 2)
    //             {
    //                 $mode = "編輯次數";
    //             }
    //             $price_data = [
    //                 'user' => $user->name,
    //                 'company' => $price_company->name,
    //                 'name' => $price->price_name,
    //                 'times' => $price->price_times,
    //                 'price' => $price->price_money,
    //                 'mode' => $mode
    //             ];
    //             array_push($price_array, $price_data);
    //         }

    //         return [
    //             'success' => true,
    //             'message' => $price_array
    //         ];  
            
    //     }else{
    //         abort(415);
    //         return [
    //             'success' => false,
    //             'message' => "請重新登入"
    //         ];
    //     }
    // }

    public function getPrice(Request $request){
        $validator = Validator::make($request->all(),[
            'token' => 'required',
        ]);

        if (!$request->token)
            abort(415);

        if($validator->fails()){
            return [
                'success' => false,
                'message' => __('register.failed'),
                'errors'=> $validator->errors()->toArray()
            ];
        }

        $company = companies::where('token', $request->token)->first();
        if($company)
        {
            $prices = price_menu::all();

            return [
                'success' => true,
                'message' => $prices
            ];  
            
        }else{
            abort(415);
            return [
                'success' => false,
                'message' => "請重新登入"
            ];
        }
    }

    public function removePrice(Request $request){
        $validator = Validator::make($request->all(),[
            'token' => 'required',
            'id' => 'required',
        ]);

        if (!$request->token)
            abort(415);

        $company = companies::where('token',$request->token)->first();
        if($company)
        {
            if($company->level == 0)
            {
                $prices = price_menu::find($request->id);
                $prices_count = $prices->count();
                
                if($prices_count == 1)
                {
                    return [
                        'success' => false,
                        'message' => "方案數量不能少於一個"
                    ];  
                }

                $prices = $prices->delete();

                if ($prices) {
                    return [
                        'success' => true,
                        'message' => "刪除成功"
                    ]; 
                } else {
                    return [
                        'success' => false,
                        'message' => "檔案已被刪除"
                    ]; 
                }
                
            }else
            {
                return [
                    'success' => false,
                    'message' => "權限不足，無法進行此操作"
                ]; 
            }
        }else{
            abort(415);
            return [
                'success' => false,
                'message' => "請重新登入"
            ];
        }
    }

    public function editPrice(Request $request){
        $validator = Validator::make($request->all(),[
            'token' => 'required',
            'id' => 'required',
            'priceName' => 'required',
            'times' => 'required',
            'price' => 'required',
            'bonus_times' => 'required'
        ]);

        if (!$request->token)
            abort(415);

        $company = companies::where('token',$request->token)->first();
        if($company)
        {
            if($company->level == 0)
            {
                $price = price_menu::where('id', $request->id)->first();

                $price->price = $request->price;
                $price->name = $request->priceName;
                $price->times = $request->times;
                $price->bonus_times = $request->bonus_times;
                $price->save();

                if ($price) {
                    return [
                        'success' => true,
                        'message' => "修改成功"
                    ]; 
                } else {
                    return [
                        'success' => false,
                        'message' => "Record not found."
                    ]; 
                } 

            }else
            {
                return [
                    'success' => false,
                    'message' => "權限不足，無法進行此操作"
                ]; 
            }
        }else{
            abort(415);
            return [
                'success' => false,
                'message' => "請重新登入"
            ];
        }

    }

    public function userAddTimes(Request $request){
        $validator = Validator::make($request->all(),[
            'token' => 'required',
            'user_id' => 'required',
            'plan_id' => 'required'
        ]);

        if (!$request->token)
            abort(415);

        if($validator->fails()){
            return [
                'success' => false,
                'message' => __('register.failed'),
                'errors'=> $validator->errors()->toArray()
            ];
        }
        
        $company = companies::where('token',$request->token);
        
        if(!$company->exists())
        {
            abort(415);
            return [
                'success' => false,
                'message' => "請重新登入"
            ];
        }

        $user = User::where('id', $request->user_id)->first();
        $plan = price_menu::where('id',$request->plan_id)->first();

        if(!$user)
        {
            return [
                'success' => false,
                'message' => "找不到此顧客"
            ]; 
        }

        if(!$plan)
        {
            return [
                'success' => false,
                'message' => "找不到此付費方案"
            ]; 
        }

        $user->download_time = $user->download_time + $plan->times;
        $user->bonus_times = $user->bonus_times + $plan->bonus_times;
        $user->save();

        return [
            'success' => true,
            'message' => "加值成功"
        ];      
    }

    public function getCompanyUser(Request $request){
        $validator = Validator::make($request->all(),[
            'token' => 'required'
        ]);

        if (!$request->token)
            abort(415);

        if($validator->fails()){
            return [
                'success' => false,
                'message' => __('register.failed'),
                'errors'=> $validator->errors()->toArray()
            ];
        }

        $getCompanyUser_Result =[
            'success' => true,
            'hasRemainingTimes'=>[],
            'hasntRemainingTimes'=>[]
        ];

        $company = companies::where('token',$request->token)->first();
        
        if(!$company)
        {
            abort(415);
            return [
                'success' => false,
                'message' => "請重新登入"
            ];
        }

        if ($company->level == 0) {
            $company_users = companies_user::orderBy('created_at', 'desc')->get();
        } else {
            $company_users = companies_user::where('company_id', $company->id)
                                           ->orderBy('created_at', 'desc')
                                           ->get();
        }
        // if($company->level == 0)
        // {
        //     $company_users = companies_user::orderBy('user_id', 'desc')->get();
        // }else
        // {
        //     $company_users = companies_user::where('company_id', $company->id)
        //                         ->orderBy('user_id', 'desc')
        //                         ->get();
        // }

        foreach ($company_users as $company_user) {
            
            $user = User::where('id',$company_user->user_id)->first();
            if (!$user->remember_token)
            {
                $token = $user->createToken($user->account)->plainTextToken;
                $user->remember_token = $token;
                $user->save();
            }
            $card = cards::where('user_id',$user->id);
            $card_amount = $card->count();

            $company_name = "";
            $company_token = "";

            $card = $card->first();
            if ($card) {
                $company_name = companies::where('id', $card->company_id)->select("name")->first()->name;
                $company_token = companies::where('id', $card->company_id)->select("token")->first()->token;
            }

            $user_data = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'company' => $company_name,
                'company_token' => $company_token,
                'company_id' => $card->company_id,
                'card_amount' => $card_amount,
                'remainingTimes' => $user->download_time + $user->bonus_times,
                'token' => $user->remember_token
            ];

            if($user->download_time == 0 && $user->bonus_times == 0)
            {
                array_push($getCompanyUser_Result['hasntRemainingTimes'], $user_data);
            }else
            {
                array_push($getCompanyUser_Result['hasRemainingTimes'], $user_data);
            }
        }
        
        return[
            'success' => true,
            'message' => $getCompanyUser_Result
        ];
    }
    public function getCompanyUserAllBC(Request $request){
        $validator = Validator::make($request->all(),[
            'token' => 'required'
        ]);

        if (!$request->token)
            abort(415);

        if($validator->fails()){
            return [
                'success' => false,
                'message' => __('register.failed'),
                'errors'=> $validator->errors()->toArray()
            ];
        }

        $getCompanyUser_Result =[
            'success' => true,
            'hasRemainingTimes'=>[],
            'hasntRemainingTimes'=>[]
        ];

        $company = companies::where('token',$request->token)->first();
        
        if(!$company)
        {
            abort(415);
            return [
                'success' => false,
                'message' => "請重新登入"
            ];
        }

        if ($company->level == 0) {
            $company_users = companies_user::select('user_id', \DB::raw('MAX(created_at) as latest_created_at'))
            ->groupBy('user_id')
            ->orderBy('latest_created_at', 'desc')
            ->get();
        } else {
            $company_users = companies_user::where('company_id', $company->id)
                                           ->orderBy('created_at', 'desc')
                                           ->get();
        }
        // if($company->level == 0)
        // {
        //     $company_users = companies_user::orderBy('user_id', 'desc')->get();
        // }else
        // {
        //     $company_users = companies_user::where('company_id', $company->id)
        //                         ->orderBy('user_id', 'desc')
        //                         ->get();
        // }

        foreach ($company_users as $company_user) {
            
            $user = User::where('id',$company_user->user_id)->first();
            if (!$user->remember_token)
            {
                $token = $user->createToken($user->account)->plainTextToken;
                $user->remember_token = $token;
                $user->save();
            }
            $card = cards::where('user_id',$user->id);
            $card_amount = $card->count();

            $company_name = "";
            $company_token = "";

            $card = $card->first();
            

            $cards = cards::where('user_id', $user->id);
            $cards = $cards->get();  
            
            $getAllBC_Result = [];
            
            foreach ($cards as $card) {
                $newData  = $card->email;
                $name = $card->name;
               
                $company_name = companies::where('id', $card->company_id)->select("name")->first()->name;
                $company_token = companies::where('id', $card->company_id)->select("token")->first()->token;
                
                // $newData = [
                //     "id"   => $card->id,
                //     "company_id" => $card->company_id,
                //     "public_id"   => $card->public_id,
                //     "name" => $card->edit_name,
                //     "releaseName" => $card->release_name,
                //     "updated_at" => $card->updated_at,
                //     "created_at" => $card->created_at,
                //     "download_times" => $card->download_time
                // ];

                $newData = [
                    "id"   => $card->id,
                    "company_id" => $card->company_id,
                    'company_token' => $company_token,
                    'company' => $company_name,
                    "public_id"   => $card->public_id,
                    "name" => $card->name,
                    "releaseName" => $card->release_name,
                    "updated_at" => $card->updated_at,
                    "created_at" => $card->created_at,
                    "download_times" => $card->download_time
                ];

                array_push($getAllBC_Result, $newData);
            }

            // $user_data = [
            //     'id' => $user->id,
            //     'name' => $user->name,
            //     'email' => $user->email,
            //     'company' => $company_name,
            //     'company_token' => $company_token,
            //     'company_id' => $card->company_id,
            //     'card_amount' => $card_amount,
            //     'remainingTimes' => $user->download_time + $user->bonus_times,
            //     'token' => $user->remember_token,
            //     'all_bc' => $getAllBC_Result
            // ];

            $user_data = [
                'id' => $user->id,
                'email' => $user->email,
                "name" => $user->name,
                'card_amount' => $card_amount,
                'remainingTimes' => $user->download_time + $user->bonus_times,
                'token' => $user->remember_token,
                'all_bc' => $getAllBC_Result
            ];

            if($user->download_time == 0 && $user->bonus_times == 0)
            {
                array_push($getCompanyUser_Result['hasntRemainingTimes'], $user_data);
            }else
            {
                array_push($getCompanyUser_Result['hasRemainingTimes'], $user_data);
            }
        }
        
        return[
            'success' => true,
            'message' => $getCompanyUser_Result
        ];
    }
    public function addCompanyUser(Request $request){
        $validator = Validator::make($request->all(),[
            'token' => 'required',
            'user_id' => 'required'

        ]);

        if (!$request->token)
            abort(415);

        if($validator->fails()){
            return [
                'success' => false,
                'message' => __('register.failed'),
                'errors'=> $validator->errors()->toArray()
            ];
        }
        
        $company = companies::where('token',$request->token)->first();
        if(!$company)
        {
            abort(415);
            return [
                'success' => false,
                'message' => "請重新登入"
            ];
        }

        $user = User::where('id',$request->user_id)->first();

        $companies_user = companies_user::where('company_id',$company->id)->where('user_id',$user->id);
        if($companies_user->exists())
        {
            return [
                'success' => false,
                'message' => "帳號已存在，無法新建"
            ];
        }
        $companies_user = $companies_user->get();

        $company_user = new companies_user();
        $company_user->user_id = $user->id;
        $company_user->company_id = $company->id;
        $company_user->save();
        return [
            'success' => true,
            'message' => "success"
        ];
    }

    public function removeCompanyUser(Request $request){
        $validator = Validator::make($request->all(),[
            'token' => 'required',
            'user_id' => 'required'
        ]);

        if (!$request->token)
            abort(415);

        if($validator->fails()){
            return [
                'success' => false,
                'message' => __('register.failed'),
                'errors'=> $validator->errors()->toArray()
            ];
        }
        
        $company = companies::where('token', $request->token)->first();
        if(!$company)
        {
            abort(415);
            return [
                'success' => false,
                'message' => "請重新登入"
            ];
        }

        $user = User::where('id',$request->user_id)->first();
        
        $companies_user = companies_user::where('company_id', $company->id)->where('user_id', $user->id)->first();
        if(!$companies_user)
        {
            return [
                'success' => false,
                'message' => "找不到此會員"
            ];
        }

        $companies_user->delete();
        return [
            'success' => true,
            'message' => "刪除成功"
        ];
    }

    public function getUserInfo(Request $request){
        $user = Auth::user();
        return[
            'success' =>true,
            'message'=>auth::user()
        ];
    }

    public function getAllBC(Request $request){
        $user = Auth::user();
        $BC_cards = cards::where('user_id', $user->id);
        
        $getAllBC_Result = [
            'bearerToken_requery' => true,
            'input_param' => [
                
            ],
            "success" => [
                'success' => true,
                'message' => []
            ],
            "failed" => [
                'success' => false,
                'message' => "getAllBC error"
            ],
        ];

        if ($request->token) {
            // 先檢查是否為公司
            $company = companies::where('token',$request->token)->first();
            if(!$company)
            {
                return [
                    'success' => false,
                    'message' => "請重新登入"
                ]; 
            }

            // 檢查是否為總公司
            if($company->level != 0)
                $BC_cards = $BC_cards->where('company_id', $company->id);       
        }   
        $BC_cards = $BC_cards->get();   
        
        foreach ($BC_cards as $BC_card) {
            $newData  = $BC_card->email;
            $name = $BC_card->name;
            
            $newData = [
                "id"   => $BC_card->id,
                "company_id" => $BC_card->company_id,
                "public_id"   => $BC_card->public_id,
                "name" => $BC_card->edit_name,
                "releaseName" => $BC_card->release_name,
                "updated_at" => $BC_card->updated_at,
                "created_at" => $BC_card->created_at,
                "download_times" => $BC_card->download_time
            ];
            array_push($getAllBC_Result['success']['message'], $newData);
        }

        return [
            $getAllBC_Result['success']
        ];

    }

    public function addBC(Request $request){
        $user = Auth::user();

        $validator = Validator::make($request->all(),[
            'token' => 'required'
        ]);

        if (!$request->token)
            abort(415);

        if($validator->fails()){
            return [
                'success' => false,
                'message' => __('register.failed'),
                'errors'=> $validator->errors()->toArray()
            ];
        }

        $company = companies::where('token',$request->token)->first();
        if(!$company)
        {
            abort(415);
            return [
                'success' => false,
                'message' => "請重新登入"
            ];
        }

        $card = new cards();

        $card->user_id = $user->id;
        $card->public_id = $user->id.Str::random(20);
        $card->is_actived = true;
        $card->company_id = $company->id;

        $card->save();

        $addBC_Result = [
            'bearerToken_requery' => true,
            'input_param' => [
                
            ],
            "success" => [
                'success' => true,
                'message' => [
                    'id' => $card->id,
                    'public_id' => $card->public_id
                ]
            ],
            "failed" => [
                'success' => false,
                'message' => "addBC error"
            ],
        ];

        return [
            $addBC_Result['success']
        ];
    }

    public function editBC(Request $request){

        // $user = Auth::user();
        // TODO 檢查該BC是否為該 user 的 card

        $validator = Validator::make($request->all(),[
            'token' => 'required',
            'id' => 'required',
            'card' => 'required'
        ]);

        if (!$request->token)
            abort(415);

        if($validator->fails()){
            return [
                'success' => false,
                'message' => __('register.failed'),
                'errors'=> $validator->errors()->toArray()
            ];
        }

        $BC_id = $request->id;
        $card_info = $request->card;
        $user = Auth::user();

        $company = companies::where('token',$request->token)->first();
        if(!$company)
        {
            abort(415);
            return [
                'success' => false,
                'message' => "請重新登入"
            ];
        }

        $card = cards::where('id', $BC_id)->first();

        if($card)
        {
            if($company->level != 0)
            {
                if($card->company_id != $company->id)
                {
                    return [
                        'success' => false,
                        'message' => "無法編輯此卡片"
                    ];
                }
            }
        }

        $get_cards = cards::where('company_id',$card->company_id)->where('email',$card_info['email'])->get()->count();
        
        if($get_cards > 1)
        {
            return[
                "failed" => [
                    'success' => false,
                    'message' => "此信箱已在本公司註冊過"
                ]
            ];
        }

       
        
        // return[
        //     'success' => true,
        //     'message' => $card->get()
        // ];
        // if($card->user_id != $user->id)
        // {
        //     return[
        //         "failed" => [
        //             'success' => false,
        //             'message' => "editBC error"
        //         ]
        //     ];
        // }     
        $logMsg = [
            'company_id' => $card->company_id,
            'user_id' => $user->id,
            'card_info' => $card_info
        ];
        Log::info($logMsg);

        // TODO 檢查該 各個ID 是否為該 user 的 ID

        // $card->name = $card_info['name'] ?? $card->name;
        // $card->email = $card_info['email'] ?? $card->email;
        // $card->phone = $card_info['phone'] ?? $card->phone;
        // $card->address = $card_info['address'] ?? $card->address;
        // $card->fax = $card_info['fax'] ?? $card->fax;
        // $card->edit_name = $card_info['edit_name'] ?? $card->edit_name;
        // $card->release_name = $card_info['release_name'] ?? $card->release_name;
        // $card->model_id = $card_info['model_id'] ?? $card->model_id;
        // $card->card_front_id = $card_info['card_front_id'] ?? $card->card_front_id;
        // $card->card_back_id = $card_info['card_back_id'] ?? $card->card_back_id;
        // $card->telegram = $card_info['telegram'] ?? $card->telegram;
        // $card->whatsapp = $card_info['whatsapp'] ?? $card->whatsapp;
        // $card->facebook = $card_info['facebook'] ?? $card->facebook;
        // $card->instagram = $card_info['instagram'] ?? $card->instagram;
        // $card->X = $card_info['X'] ?? $card->X;
        // $card->web = $card_info['web'] ?? $card->web;
        // $card->is_actived = $card_info['is_actived'] ?? $card->is_actived;
        $user->name = array_key_exists('name', $card_info) ? $card_info['name'] : $card->name;
        $user->email = array_key_exists('email', $card_info) ? $card_info['email'] : $card->email;
        $user->phone = array_key_exists('phone', $card_info) ? $card_info['phone'] : $card->phone;

        $card->name = array_key_exists('name', $card_info) ? $card_info['name'] : $card->name;
        $card->email = array_key_exists('email', $card_info) ? $card_info['email'] : $card->email;
        $card->phone = array_key_exists('phone', $card_info) ? $card_info['phone'] : $card->phone;
        $card->address = array_key_exists('address', $card_info) ? $card_info['address'] : $card->address;
        $card->fax = array_key_exists('fax', $card_info) ? $card_info['fax'] : $card->fax;
        $card->edit_name = array_key_exists('edit_name', $card_info) ? $card_info['edit_name'] : $card->edit_name;
        $card->release_name = array_key_exists('release_name', $card_info) ? $card_info['release_name'] : $card->release_name;
        $card->model_id = array_key_exists('model_id', $card_info) ? $card_info['model_id'] : $card->model_id;
        $card->card_front_id = array_key_exists('card_front_id', $card_info) ? $card_info['card_front_id'] : $card->card_front_id;
        $card->card_back_id = array_key_exists('card_back_id', $card_info) ? $card_info['card_back_id'] : $card->card_back_id;
        $card->telegram = array_key_exists('telegram', $card_info) ? $card_info['telegram'] : $card->telegram;
        $card->whatsapp = array_key_exists('whatsapp', $card_info) ? $card_info['whatsapp'] : $card->whatsapp;
        $card->facebook = array_key_exists('facebook', $card_info) ? $card_info['facebook'] : $card->facebook;
        $card->instagram = array_key_exists('instagram', $card_info) ? $card_info['instagram'] : $card->instagram;
        $card->X = array_key_exists('X', $card_info) ? $card_info['X'] : $card->X;
        $card->line = array_key_exists('line', $card_info) ? $card_info['line'] : $card->line;
        $card->web = array_key_exists('web', $card_info) ? $card_info['web'] : $card->web;
        $card->wechat = array_key_exists('wechat', $card_info) ? $card_info['wechat'] : $card->wechat;
        $card->tiktok = array_key_exists('tiktok', $card_info) ? $card_info['tiktok'] : $card->tiktok;
        $card->youtube = array_key_exists('youtube', $card_info) ? $card_info['youtube'] : $card->youtube;
        $card->is_actived = array_key_exists('is_actived', $card_info) ? $card_info['is_actived'] : $card->is_actived;


        // foreach ($card_info as $key => $info) {
        //     $card->$key = $info;
        // }
        $user->save();
        $card->save();
        $card = cards::find($BC_id);

        return[
            'success' => true,
            'message' => $card
        ];
    }

    public function removeBC(Request $request){
        $user = Auth::user();
        // TODO 檢查該BC是否為該 user 的 card

        $validator = Validator::make($request->all(),[
            'token' => 'required',
            'id' => 'required'
        ]);

        if (!$request->token)
            abort(415);
        
        if($validator->fails()){
            return [
                'success' => false,
                'message' => __('register.failed'),
                'errors'=> $validator->errors()->toArray()
            ];
        }

        $company = companies::where('token',$request->token)->first();
        
        if(!$company)
        {
            abort(415);
            return [
                'success' => false,
                'message' => "請重新登入"
            ];
        }

        $card = cards::where('id',$request->id)->first();
              
        if($card)
        {
            if($company->level != 0)
            {
                if($card->company_id != $company->id)
                {
                    return [
                        'success' => false,
                        'message' => "無法編輯此卡片"
                    ];
                }
            }
        }else{
            return[
                'success' => false,
                'message' => "找不到此卡片"
            ];
        }

        try{
            $model = models::where('id', $card->model_id)->first();
            if ($model)
            {
                if ($model->mesh_url) {
                    if (Storage::disk('s3')->exists($model->mesh_url))
                        Storage::disk('s3')->delete($model->mesh_url);
                }

                if ($model->texture_url) {
                    if (Storage::disk('s3')->exists($model->texture_url))
                        Storage::disk('s3')->delete($model->texture_url);
                }

                if ($model->cover_url) {
                    if (Storage::disk('s3')->exists($model->cover_url))
                        Storage::disk('s3')->delete($model->cover_url);
                }

                if ($model->cover_half_url) {
                    if (Storage::disk('s3')->exists($model->cover_half_url))
                        Storage::disk('s3')->delete($model->cover_half_url);
                }
                
                $model->delete();
            }
            
            // card_front_id
            $card_front = materials::where('id', $card->card_front_id)->first();
            if ($card_front)
            {
                if ($card_front->card_url) {
                    if (Storage::disk('s3')->exists($card_front->card_url))
                        Storage::disk('s3')->delete($card_front->card_url);
                }  
                $card_front->delete();
            }

            // card_back_id
            $card_back = materials::where('id', $card->card_back_id)->first();
            if ($card_back)
            {
                if ($card_back->card_url) {
                    if (Storage::disk('s3')->exists($card_back->card_url))
                        Storage::disk('s3')->delete($card_back->card_url);
                }  
                $card_back->delete();
            }

            $id = $card->id;
            $card->delete();
            
            return[
                'success' => true,
                'message' => $id
            ];
        }
        catch(Exception $e){
            return[
                'success' => false,
                'message' => $e
            ];
        }
    }

    public function addMaterials(Request $request){
        $validator = Validator::make($request->all(),[
            'token' => 'required',
            'card_url' => 'required'
        ]);

        if (!$request->token)
            abort(415);

        if($validator->fails()){
            return [
                'success' => false,
                'message' => __('register.failed'),
                'errors'=> $validator->errors()->toArray()
            ];
        }
  
        $company = companies::where('token',$request->token)->first();
        if(!$company)
        {
            abort(415);
            return [
                'success' => false,
                'message' => "請重新登入"
            ];
        }

        $user = Auth::user();
        if (!$user) {
            return [
                'success' => false,
                'message' => "plz login",
            ];
        }
        
        // $user = User::where('account', $request->user_account)->first();

        // 取得卡片圖並儲存到 S3
        $id = uniqid();
        $s3_dir = env('APP_ENV') . "/".$user->id."/"."material"."/"; // 修改这里的设定
        $s3_fileName = $id . '.png';
        $s3_url = $s3_dir . $s3_fileName;

        $uploadedFile = $request->file('card_url');

        if ($uploadedFile->isValid()) {
            $upload_res = $uploadedFile->storeAs($s3_dir, $s3_fileName, 's3'); 
            if (!$upload_res) 
            {
                return [
                    'success' => false,
                    'message' => [
                    ],
                ];
            }
            
            // $file = new \Illuminate\Http\UploadedFile($tmpFilePath, $s3_fileName, 'image/png', null, true);
            // $file->storeAs($s3_dir, $s3_fileName, 's3'); // 修改这里的存储方式
            
            $material = new materials();
            $material->user_id = $user->id;
            $material->card_url = $s3_url;
            $material->company_id = $company->id;
            $material->save();

            return [
                'success' => true,
                'message' => [
                    'id' => $material->id
                ],
            ];
        }
        else {
            return [
                'success' => false,
                'message' => [
                ],
            ];
        }

        // $image = Image::make($request->card_url);
        // $image->save($tmpFilePath);

        
        return[
            'filename:'=>$request->card_url->getClientOriginalName()
        ];
        $material = new materials();
        $material->user_id = $user->id;
        $material->card_url = $request->card_url;

        $material->save();

        return [
            'success' => true,
            'message' => [
                'user_id' => $material->user_id,
                'card_url' => $material->card_url,
            ],
        ];
    }

    public function addModels(Request $request){
        $validator = Validator::make($request->all(),[
            'token' => 'required',
            'user_account' => 'required',
            'texture_url' => 'required',
            'mesh_url' => 'required',
            'cover_url' => 'required',
            'cover_half_url' => 'required',
        ]);

        if (!$request->token)
            abort(415);

        if($validator->fails()){
            return [
                'success' => false,
                'message' => __('register.failed'),
                'errors'=> $validator->errors()->toArray()
            ];
        }
  
        $company = companies::where('token',$request->token)->first();
        if(!$company)
        {
            abort(415);
            return [
                'success' => false,
                'message' => "請重新登入"
            ];
        }

        $user = User::where('account', $request->user_account)->first();
        if (!$user) {
            return [
                'success' => false,
                'message' => "查無此帳號，請確認帳號是否輸入正確",
            ];
        }

        $id = uniqid();
        $s3_model_dir = env('APP_ENV') . "/".$user->id."/"."model"."/".$id."/"; // 修改这里的设定

        $s3_texture_dir = $s3_model_dir."texture";
        $s3_mesh_dir = $s3_model_dir."mesh";
        $s3_cover_dir = $s3_model_dir."cover";
        $s3_coverHalf_dir = $s3_model_dir."cover_half";

        $textureFile = $request->file('texture_url');
        $meshFile = $request->file('mesh_url');
        $coverFile = $request->file('cover_url');
        $coverHalfFile = $request->file('cover_half_url');

        $s3_texture_fileName = $id . '.' . $textureFile->getClientOriginalExtension();
        $s3_mesh_fileName = $id . '.' . $meshFile->getClientOriginalExtension();
        $s3_cover_fileName = $id . '.' . $coverFile->getClientOriginalExtension();
        $s3_coverHalf_fileName = $id . '.' . $coverHalfFile->getClientOriginalExtension();

        if (!($textureFile->isValid() && $meshFile->isValid() && $coverFile->isValid() && $coverHalfFile->isValid()))
        {
            return [
                'success' => false,
                'message' => "檔案上傳失敗，請確認您上傳的檔案是否可用",
            ];
        }

        $isAllUploaded = true;
        
        if (!$textureFile->storeAs($s3_texture_dir, $s3_texture_fileName, 's3'))
            $isAllUploaded = false;
        if (!$meshFile->storeAs($s3_mesh_dir, $s3_mesh_fileName, 's3'))
            $isAllUploaded = false;
        if (!$coverFile->storeAs($s3_cover_dir, $s3_cover_fileName, 's3'))
            $isAllUploaded = false;
        if (!$coverHalfFile->storeAs($s3_coverHalf_dir, $s3_coverHalf_fileName, 's3'))
            $isAllUploaded = false; 

        if (!$isAllUploaded)
        {
            // 刪掉該 $s3_model_dir 資料夾
            return [
                'success' => false,
                'message' => "檔案上傳失敗，請確認您上傳的檔案是否可用",
            ];
        }

        $model = new models();
        $model->user_id = $user->id;
        $model->texture_url = $s3_texture_dir.'/'.$s3_texture_fileName;
        $model->mesh_url = $s3_mesh_dir.'/'.$s3_mesh_fileName;
        $model->cover_url = $s3_cover_dir.'/'.$s3_cover_fileName;
        $model->cover_half_url = $s3_coverHalf_dir.'/'.$s3_coverHalf_fileName;
        $model->company_id = $company->id;

        $model->save();

        return [
            'success' => true,
            'message' => [
                'user_id' => $model->user_id,
                'mesh_url' => $model->mesh_url,
                'texture_url' => $model->texture_url,
                'cover_url' => $model->cover_url,
                'id' => $model->id
            ],
        ];
    }

    public function editMaterials(Request $request){
        $validator = Validator::make($request->all(),[
            'BC_id' => 'required',
            'token' => 'required',
            'card_url' => 'required',
            'id' => 'required'
        ]);

        if (!$request->token)
            abort(415);

        if($validator->fails()){
            return [
                'success' => false,
                'message' => __('editMaterials.failed'),
                'errors'=> $validator->errors()->toArray()
            ];
        }

        $company = companies::where('token',$request->token)->first();
        if(!$company)
        {
            abort(415);
            return [
                'success' => false,
                'message' => "請重新登入"
            ];
        }
        
        $material = materials::where('id',$request->id)->first();

        if(!$material)
        {
            return[
                'success' => false,
                'message' => "找不到卡片"
            ];
        }

        $card = cards::where('id',$request->BC_id)->first();
        if($card)
        {
            $card->version = $card->version + 1;
            $card->save();
        }

        // $user = User::where('account', $request->user_account)->first();

        // 取得卡片圖並儲存到 S3

        $fullPath = $material->card_url;

        // 使用 PHP 的 pathinfo 函数分割路径和文件名
        $pathInfo = pathinfo($fullPath);

        $s3_dir = $pathInfo['dirname']; // 获取路径
        $s3_fileName = $pathInfo['basename']; // 获取文件名

        $uploadedFile = $request->file('card_url');

        if ($uploadedFile->isValid()) {
            $uploadedFile->storeAs($s3_dir, $s3_fileName, 's3'); 
            // $file = new \Illuminate\Http\UploadedFile($tmpFilePath, $s3_fileName, 'image/png', null, true);
            // $file->storeAs($s3_dir, $s3_fileName, 's3'); // 修改这里的存储方式
            
            return [
                'success' => true,
                'message' => [
                    'id' => $material->id
                ],
            ];
        }
        else {
            return [
                'success' => false,
                'message' => [
                ],
            ];
        }

    }

    public function editModels(Request $request){
        $validator = Validator::make($request->all(),[
            'BC_id' => 'required',
            'token' => 'required',
            'id' => 'required',
            'texture_url' => 'required',
            'mesh_url' => 'required',
            'cover_url' => 'required',
            'cover_half_url' => 'required',
        ]);

        if (!$request->token)
            abort(415);

        if($validator->fails()){
            return [
                'success' => false,
                'message' => __('editModels.failed'),
                'errors'=> $validator->errors()->toArray()
            ];
        }

        $company = companies::where('token',$request->token)->first();
        if(!$company)
        {
            abort(415);
            return [
                'success' => false,
                'message' => "請重新登入"
            ];
        }

        $model = models::where('id',$request->id)->first();
        
        if(!$model)
        {
            return[
                'success' => false,
                'message' => "找不到編輯的模型"
            ];
        }

        $card = cards::where('id',$request->BC_id)->first();
        if($card)
        {
            $card->version = $card->version + 1;
            $card->save();
        }

        // 取得卡片圖並儲存到 S3

        $fullPath_mesh = $model->mesh_url;
        $pathInfo = pathinfo($fullPath_mesh);
        $s3_mesh_dir = $pathInfo['dirname']; 
        $s3_mesh_fileName = $pathInfo['basename']; 
        $meshFile = $request->file('mesh_url');

        $fullPath_texture = $model->texture_url;
        $pathInfo = pathinfo($fullPath_texture);
        $s3_texture_dir = $pathInfo['dirname']; 
        $s3_texture_fileName = $pathInfo['basename']; 
        $textureFile = $request->file('texture_url');

        $fullPath_cover = $model->cover_url;
        $pathInfo = pathinfo($fullPath_cover);
        $s3_cover_dir = $pathInfo['dirname']; 
        $s3_cover_fileName = $pathInfo['basename']; 
        $coverFile = $request->file('cover_url');

        $fullPath_cover_half = $model->cover_half_url;
        $pathInfo = pathinfo($fullPath_cover_half);
        $s3_coverHalf_dir = $pathInfo['dirname']; 
        $s3_coverHalf_fileName = $pathInfo['basename']; 
        $coverHalfFile = $request->file('cover_half_url');

        if(!($textureFile->isValid() && $meshFile->isValid() && $coverFile->isValid() && $coverHalfFile->isValid())) {
            $error = "";
            if (!$textureFile->isValid())
                $error = $error."貼圖 ";
            
            if (!$meshFile->isValid())
                $error = $error."模型 ";

            if (!$coverFile->isValid())
                $error = $error."全身圖 ";

            if (!$coverHalfFile->isValid())
                $error = $error."半身圖 ";

            return [
                'success' => false,
                'message' => "找不到".$error
            ];
        }

        $textureFile->storeAs($s3_texture_dir, $s3_texture_fileName, 's3'); 
        $meshFile->storeAs($s3_mesh_dir, $s3_mesh_fileName, 's3'); 
        $coverFile->storeAs($s3_cover_dir, $s3_cover_fileName, 's3'); 
        $coverHalfFile->storeAs($s3_coverHalf_dir, $s3_coverHalf_fileName, 's3'); 
            // $file = new \Illuminate\Http\UploadedFile($tmpFilePath, $s3_fileName, 'image/png', null, true);
            // $file->storeAs($s3_dir, $s3_fileName, 's3'); // 修改这里的存储方式

        return [
            'success' => true,
            'message' => [
                'user_id' => $model->user_id,
                'mesh_url' => $model->mesh_url,
                'texture_url' => $model->texture_url,
                'cover_url' => $model->cover_url,
                'id' => $model->id
            ],
        ];
        
    }

    public function getMaterial(Request $request){
        $user = Auth::user();
        
        $materials = materials::where('user_id', $user->id)->get();
        $models = models::where('user_id', $user->id)->get();
       
        $getMaterial_Result = [
            'bearerToken_requery' => true,
            'input_param' => [
            ],
            "success" => [
                'success' => true,
                'message' => [
                    "modelList" => [],
                    "cardPicList" => []
                ]
            ],
            "failed" => [
                'success' => false,
                'message' => "getMaterial error"
            ],
        ];
        
        foreach ($materials as $material) {
            $newData = [
                "id"   => $material->id,
                "url" => Storage::disk('s3')->temporaryUrl($material->card_url, now()->addHour()),
            ];
            array_push($getMaterial_Result['success']['message']['cardPicList'], $newData);
        }
       
        foreach ($models as $model) {
            $newData = [
                "id"   => $model->id,
                // "texture" => Storage::disk('s3')->temporaryUrl($model->texture_url, now()->addHour()),
                // "mesh" => Storage::disk('s3')->temporaryUrl($model->mesh_url, now()->addHour()),
                "cover" => Storage::disk('s3')->temporaryUrl($model->cover_url, now()->addHour()),
            ];
            array_push($getMaterial_Result['success']['message']['modelList'], $newData);
        }
        return[
            $getMaterial_Result['success']
         ];
    }

    public function getBC_info(Request $request) {
        $BC_id = $request->input('id');
        $card = null;
        if (!$BC_id) {
            $BC_id = $request->input('public_id');
            $card = cards::where('public_id', $BC_id)->first();
        }
        else
            $card = cards::where('id',$BC_id)->first();

        if (!$card) {
            return [
                'success' => false,
                'message' => "查無此名片資料"
            ];
        }

        if (!$card->is_actived)
        {
            return [
                'success' => false,
                'message' => "該名片並未開放"
            ];
        }
        $user = User::where('id',$card->user_id)->first();
        $model = models::where('id',$card->model_id)->first();
        $result = [
            'success' => true,
            'message' => 
            [
                "version" => $card->version,
                "release_name"=> $card->release_name,
                "download_times" => $card->download_time,
                "remainingTimes" => $user->download_time,
                "is_actived" => $card->is_actived,
                "model" => [
                    "cover_half" => $model->cover_half_url ?? ''
                ],
            ]
        ];

        $new_data = [
            'fax' => $card->fax,
            'address' => $card->address,
            'telegram' => $card->telegram,
            'whatsapp' => $card->whatsapp,
            'instagram' => $card->instagram,
            'facebook' => $card->facebook,
            'X' => $card->X,
            'web' => $card->web,
            'line' => $card->line,
            'name' => $card->name,
            'email' => $card->email,
            'phone' => $card->phone,
            'wechat' => $card->wechat,
            'tiktok' => $card->tiktok,
            'youtube' => $card->youtube
        ];
        
        foreach ($new_data as $name => $value) {
            if ($value !== null) {
                $result['message'][$name] = $value;
            }
        }

        if ($result['message']['model']['cover_half'] != '')
            $result['message']['model']['cover_half'] = Storage::disk('s3')->temporaryUrl($result['message']["model"]["cover_half"], now()->addHour());
        return $result;
    }

    public function getAllTimesByArray(Request $request){
        $validator = Validator::make($request->all(),[
            'token' => 'required',
        ]);

        if (!$request->token)
            abort(415);

        if($validator->fails()){
            return [
                'success' => false,
                'message' => __('register.failed'),
                'errors'=> $validator->errors()->toArray()
            ];
        }

        $company = companies::where('token', $request->token)->first();

        if($company)
        {
            $times_Result = [     
                'success' => true,
                'message' => []       
            ];
            
            foreach ($request->requestList as $item) {
                if(isset($item["card_id"]))
                    $card = cards::where('id', $item["card_id"])->first();
                $user = User::where('id', $item["user_id"])->first();

                $res = null;
                if(isset($card))
                {
                    if ($card && $user) {
                        $res = [
                            'company_id' => $card->company_id,
                            'user_id' => $card->user_id,
                            'user_name' => $user->name,
                            'download_times' => $card->download_time,
                            'remainingTimes' => $user->download_time,
                        ];
                    }
                }else{
                    $res = [
                        'user_id' => $user->id,
                        'user_name' => $user->name,
                        'remainingTimes' => $user->download_time,
                    ];
                }
                array_push($times_Result['message'], $res);
            }

            return $times_Result;
        }
        else
        {
            abort(415);
            return [
                'success' => false,
                'message' => "請重新登入"
            ];
        }

    }
    public function getAllTimes(Request $request){
        $validator = Validator::make($request->all(),[
            'token' => 'required'
        ]);

        if (!$request->token)
            abort(415);

        if($validator->fails()){
            return [
                'success' => false,
                'message' => __('register.failed'),
                'errors'=> $validator->errors()->toArray()
            ];
        }

        $company = companies::where('token',$request->token)->first();

        if($company)
        {
            $times_Result = [     
                'success' => true,
                'message' => []       
            ];
            if($company->level == 0)
            {
                $company_users = companies_user::all();      
            }else
            {
                $company_users = companies_user::where('company_id',$company->id)->get();
            }

            foreach ($company_users as $company_user) {
                $card = cards::where('company_id',$company_user->company_id)->where('user_id',$company_user->user_id)->first();
                $user = User::where('id',$company_user->user_id)->first();
                
                $res = [
                    'company_id' => $card->company_id,
                    'user_id' => $card->user_id,
                    'download_times' => $card->download_time,
                    'remainingTimes' => $user->download_time,
                ];
                array_push($times_Result['message'], $res);
            }

            return $times_Result;
        }else
        {
            abort(415);
            return [
                'success' => false,
                'message' => "請重新登入"
            ];
        }
    }

    public function reduceTimes(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'public_id' => 'required',
        ]);

       


        $BC_id = $request->input('public_id');
        $card = cards::where('public_id', $BC_id)->first();
        if(!$card)
        {
            return [
                'success' => false,
                'message' => "查無該名片"
            ];
        }
        
        if (!$card->is_actived)
        {
            return [
                'success' => false,
                'message' => "該名片並未開放"
            ];
        }

        $user = User::where('id', $card->user_id)->first();
        if (!$user) {
            return [
                'success' => false,
                'message' => "查無該名片資料"
            ];
        }

        $key = $request->input('key');
        if(!$key)
        {
            return [
                'success' => false,
                'message' => "查無該名片資料"
            ];
        }
        
        $rec = Key::where('public_id', $BC_id)->where('key', $request->input('key'))->first();
        if (!$rec) {
        // return you don't have the record of use get_BC api
            return [
                'success' => false,
                'message' => "觀看次數刪除失敗"
            ];
        }
        $rec->delete();

        if ($request->input('token'))
        {
            $user = User::where('remember_token', $request->token)->first();
            if ($user)
            {
                if ($user->id == $card->user_id)
                {
                    return [
                        'success' => true,
                        'message' => [
                            'remainingTimes' => $user->download_time,
                            'download_times' => $card->download_time,
                        ]
                    ];
                }
            }
        }

        if($user->download_time <= 0 && $user->bonus_times <= 0)
        {
            return [
                'success' => false,
                // 'message' => "該名片以達到使用上限，你也想要擁有3D名片嗎? 請洽 : <a href=''>法鬥文創</a>"
                'message' => "該名片以達到使用上限，你也想要擁有3D名片嗎? 請洽 :法鬥文創"
            ];
        }

        if($user->download_time >0)
        {
            $user->download_time -= 1;
        }else
        {
            $user->bonus_times -= 1;
        }
        
        $times = $user->download_time + $user->bonus_times;

        if($times == 10 || $times == 50)
        {
            $user->notify(new ClickTimesRemind($user->confirm_code, $times));
        }
        else if($times == 0)
        {
            $user->notify(new NoTimesRemind($user->confirm_code));
        }

        $user->save();

        $card->download_time += 1;
        $card->save();

        return [
            'success' => true,
            'message' => [
                'remainingTimes' => $user->download_time,
                'download_times' => $card->download_time,
            ]
        ];
    }

    public function getBC(Request $request){

        $BC_id = $request->input('id');
        $card = null;
        if (!$BC_id) {
            $BC_id = $request->input('public_id');
            $card = cards::where('public_id', $BC_id)->first();
        }
        else
            $card = cards::where('id',$BC_id)->first();

        if (!$card) {
            return [
                'success' => false,
                'message' => "查無此名片資料"
            ];
        }
        
        $isRoot = false;
        $rand_key = '';
        if ($request->input('token'))
        {
            $user = User::where('remember_token', $request->token)->first();
            if ($user)
            {
                if ($user->id != $card->user_id)
                {
                    // 傳 token 但是不是自己的 token 拒絕存取
                    return [
                        'success' => false,
                        'message' => "無法取得登入資訊，請重新登入"
                    ];
                }
                $isRoot = true;
            }
            else
            {
                return [
                    'success' => false,
                    'message' => "無法取得登入資訊，請重新登入"
                ];
            }
        }
        else
        {
            if (!$card->is_actived)
            {
                return [
                    'success' => false,
                    'message' => "該名片並未開放"
                ];
            }

            $user = User::where('id', $card->user_id)->first();
            if (!$user) {
                return [
                    'success' => false,
                    'message' => "查無該名片資料"
                ];
            }

            if($user->download_time <= 0 && $user->bonus_times <= 0)
            {
                return [
                    'success' => false,
                    // 'message' => "該名片以達到使用上限，你也想要擁有3D名片嗎? 請洽 : <a href=''>法鬥文創</a>"
                    'message' => "該名片以達到使用上限，你也想要擁有3D名片嗎? 請洽 :法鬥文創"
                ];
            }

            $rec = new Key();
            $rand_key = Str::random(60);
            $rec->key = $rand_key;
            $rec->public_id = $BC_id;

            $rec->save();

            // if(!$isRoot)
            // {
            //     if($user->download_time >0)
            //     {
            //         $user->download_time -= 1;
            //     }else
            //     {
            //         $user->bonus_times -= 1;
            //     }
            // }
            // $times = $user->download_time + $user->bonus_times;

            // if($times == 10 || $times == 50)
            // {
            //     $user->notify(new ClickTimesRemind($user->confirm_code, $times));
            // }
            // else if($times == 0)
            // {
            //     $user->notify(new NoTimesRemind($user->confirm_code));
            // }

            // $user->save();

            // $card->download_time += 1;
            // $card->save();

        }

        $model = models::where('id',$card->model_id)->first();
        $card_front = materials::where('id',$card->card_front_id)->first();
        $card_back = materials::where('id',$card->card_back_id)->first();

        
        $getBC_Result = [
            'bearerToken_requery' => false,
            'input_param' => [
                'id' => 'bc_id'
            ],
            "success" => [
                'success' => true,
                'message' => [
                    
                    "edit_name"=> $card->edit_name,
                    "release_name"=> $card->release_name,
                    "card_front_id" => $card_front->id ?? null,
                    "card_back_id" => $card_back->id ?? null,
                    "card_front" => $card_front->card_url ?? '',
                    "card_back" => $card_back->card_url ?? '',
                    "is_actived" => $card->is_actived,
                    "download_times" => $card-> download_time,
                    'remainingTimes' => $user->download_time,
                    // "address"=> $card->address,
                    // "fax"=> $card->fax,
                    // "telegram" =>$card->telegram,
                    // "whatsapp" =>$card->whatsapp,
                    // "fb" => $card->facebook,
                    // "ig" => $card->instagram,
                    // "x"  => $card->X,
                    // "web"=> $card->web,
                    // "line" => $card->line,

                    "model" => [
                        "id" => $model->id ?? null,
                        "texture" => $model->texture_url ?? '',
                        "mesh" => $model->mesh_url ?? '',
                        "cover" => $model->cover_url ?? '',
                        "cover_half" => $model->cover_half_url ?? ''
                    ],
                    'key' => $rand_key
                ]
            ],
            "failed" => [
                'success' => false,
                'message' => "getBC error"
            ],
        ];

        $social_array = [
            'fax' => $card->fax,
            'address' => $card->address,
            'telegram' => $card->telegram,
            'whatsapp' => $card->whatsapp,
            'instagram' => $card->instagram,
            'facebook' => $card->facebook,
            'X' => $card->X,
            'web' => $card->web,
            'line' =>$card->line,
            'name' => $card->name,
            'email' => $card->email,
            'phone' => $card->phone,
            'wechat' => $card->wechat,
            'tiktok' => $card->tiktok,
            'youtube' => $card->youtube
        ];
        
        if ($getBC_Result['success']['message']["card_front"] != '')
            $getBC_Result['success']['message']["card_front"] = Storage::disk('s3')->temporaryUrl($getBC_Result['success']['message']["card_front"], now()->addHour());
        if ($getBC_Result['success']['message']["card_back"] != '')
            $getBC_Result['success']['message']["card_back"] = Storage::disk('s3')->temporaryUrl($getBC_Result['success']['message']["card_back"], now()->addHour());

        if ($getBC_Result['success']['message']["model"]["texture"] != '')
            $getBC_Result['success']['message']["model"]["texture"] = Storage::disk('s3')->temporaryUrl($getBC_Result['success']['message']["model"]["texture"], now()->addHour());
        if ($getBC_Result['success']['message']["model"]["mesh"] != '')
            $getBC_Result['success']['message']["model"]["mesh"] = Storage::disk('s3')->temporaryUrl($getBC_Result['success']['message']["model"]["mesh"], now()->addHour());
        if ($getBC_Result['success']['message']["model"]["cover"] != '')
            $getBC_Result['success']['message']["model"]["cover"] = Storage::disk('s3')->temporaryUrl($getBC_Result['success']['message']["model"]["cover"], now()->addHour());
        if ($getBC_Result['success']['message']["model"]["cover_half"] != '')
            $getBC_Result['success']['message']["model"]["cover_half"] = Storage::disk('s3')->temporaryUrl($getBC_Result['success']['message']["model"]["cover_half"], now()->addHour());
    
        foreach($social_array as $name => $social)
        {
            if($social != null)
                $getBC_Result['success']['message'][$name] = $social;

        }

        if ($isRoot)
            $getBC_Result['success']['message']["download_times"] = $card->download_time;

        return [
            $getBC_Result['success']
        ];
    }

    public function getBC_admin(Request $request){

        $BC_id = $request->input('id');
        $card = null;
        if (!$BC_id) {
            $BC_id = $request->input('public_id');
            $card = cards::where('public_id', $BC_id)->first();
        }
        else
            $card = cards::where('id',$BC_id)->first();

        if (!$card) {
            return [
                'success' => false,
                'message' => "查無此名片資料"
            ];
        }
        
        $isRoot = false;
        if ($request->input('token'))
        {
            $user = User::where('remember_token', $request->token)->first();
            if ($user)
            {
                if ($user->id != $card->user_id)
                {
                    // 傳 token 但是不是自己的 token 拒絕存取
                    return [
                        'success' => false,
                        'message' => "無法取得登入資訊，請重新登入"
                    ];
                }
                $isRoot = true;
            }
            else
            {
                return [
                    'success' => false,
                    'message' => "無法取得登入資訊，請重新登入"
                ];
            }
        }
        else
        {
            if (!$card->is_actived)
            {
                return [
                    'success' => false,
                    'message' => "該名片並未開放"
                ];
            }

            $user = User::where('id', $card->user_id)->first();
            if (!$user) {
                return [
                    'success' => false,
                    'message' => "查無該名片資料"
                ];
            }
        }

        $model = models::where('id',$card->model_id)->first();
        $card_front = materials::where('id',$card->card_front_id)->first();
        $card_back = materials::where('id',$card->card_back_id)->first();

        
        $getBC_Result = [
            'bearerToken_requery' => false,
            'input_param' => [
                'id' => 'bc_id'
            ],
            "success" => [
                'success' => true,
                'message' => [
                    
                    "edit_name"=> $card->edit_name,
                    "release_name"=> $card->release_name,
                    "card_front_id" => $card_front->id ?? null,
                    "card_back_id" => $card_back->id ?? null,
                    "card_front" => $card_front->card_url ?? '',
                    "card_back" => $card_back->card_url ?? '',
                    "is_actived" => $card->is_actived,
                    "download_times" => $card-> download_time,
                    'remainingTimes' => $user->download_time,
                    // "address"=> $card->address,
                    // "fax"=> $card->fax,
                    // "telegram" =>$card->telegram,
                    // "whatsapp" =>$card->whatsapp,
                    // "fb" => $card->facebook,
                    // "ig" => $card->instagram,
                    // "x"  => $card->X,
                    // "web"=> $card->web,
                    // "line" => $card->line,

                    "model" => [
                        "id" => $model->id ?? null,
                        "texture" => $model->texture_url ?? '',
                        "mesh" => $model->mesh_url ?? '',
                        "cover" => $model->cover_url ?? '',
                        "cover_half" => $model->cover_half_url ?? ''
                    ],

                ]
            ],
            "failed" => [
                'success' => false,
                'message' => "getBC error"
            ],
        ];

        $social_array = [
            'fax' => $card->fax,
            'address' => $card->address,
            'telegram' => $card->telegram,
            'whatsapp' => $card->whatsapp,
            'instagram' => $card->instagram,
            'facebook' => $card->facebook,
            'X' => $card->X,
            'web' => $card->web,
            'line' =>$card->line,
            'name' => $card->name,
            'email' => $card->email,
            'phone' => $card->phone
        ];
        
        if ($getBC_Result['success']['message']["card_front"] != '')
            $getBC_Result['success']['message']["card_front"] = Storage::disk('s3')->temporaryUrl($getBC_Result['success']['message']["card_front"], now()->addHour());
        if ($getBC_Result['success']['message']["card_back"] != '')
            $getBC_Result['success']['message']["card_back"] = Storage::disk('s3')->temporaryUrl($getBC_Result['success']['message']["card_back"], now()->addHour());

        if ($getBC_Result['success']['message']["model"]["texture"] != '')
            $getBC_Result['success']['message']["model"]["texture"] = Storage::disk('s3')->temporaryUrl($getBC_Result['success']['message']["model"]["texture"], now()->addHour());
        if ($getBC_Result['success']['message']["model"]["mesh"] != '')
            $getBC_Result['success']['message']["model"]["mesh"] = Storage::disk('s3')->temporaryUrl($getBC_Result['success']['message']["model"]["mesh"], now()->addHour());
        if ($getBC_Result['success']['message']["model"]["cover"] != '')
            $getBC_Result['success']['message']["model"]["cover"] = Storage::disk('s3')->temporaryUrl($getBC_Result['success']['message']["model"]["cover"], now()->addHour());
        if ($getBC_Result['success']['message']["model"]["cover_half"] != '')
            $getBC_Result['success']['message']["model"]["cover_half"] = Storage::disk('s3')->temporaryUrl($getBC_Result['success']['message']["model"]["cover_half"], now()->addHour());
    
        foreach($social_array as $name => $social)
        {
            if($social != null)
                $getBC_Result['success']['message'][$name] = $social;

        }

        if ($isRoot)
            $getBC_Result['success']['message']["download_times"] = $card->download_time;

        return [
            $getBC_Result['success']
        ];
    }


    public function send_LinePay(Request $request){
        $orderId = payments::max('id');
        $account = $request->input('account');
        $currency = $request->input('currency');
        $amount = $request->input('amount');

        $user = User::where('account', $account)->select("id")->first();

        if(!$orderId)
        {
            $orderId = 0;
        }else{
            $orderId +=1;
        }


        $sandBox = 'https://sandbox-api-pay.line.me';
        $uri = '/v3/payments/request';
        $channelId = '2004609569';
        $channelSecret = '465f27ae2239db90cd82824ec66e2498';
        $Nonce = date('c') . uniqid('-');
        $isSandbox = false;
        
        $qyery = [
            'amount' => $amount,
            'currency' => 'TWD',
            'orderId' => $orderId,
            'packages' => [
                [
                    'id' => '000001',
                    'amount' => $amount,
                    'name' => 'test store',
                    'products' => [
                        [
                            'name' => 'test product',
                            'quantity' => 1,
                            'price' => $amount
                        ],
                    ],
                ],
            ],
            'redirectUrls' => [
                'confirmUrl' => 'http://192.168.0.112:8001/api/confirm.php',
                'cancelUrl' => 'https://test.astralweb.com/cancel.php',
            ],
        ];
        $authMacText = $channelSecret . $uri . json_encode($qyery) . $Nonce;
        $Authorization = base64_encode(hash_hmac('sha256', $authMacText, $channelSecret, true));
        
        
        $curl = curl_init();
        
        curl_setopt_array($curl, array(
            CURLOPT_URL => $sandBox.$uri,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>json_encode($qyery),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'X-LINE-ChannelId: '.$channelId,
                'X-LINE-Authorization-Nonce: '.$Nonce,
                'X-LINE-Authorization: '.$Authorization
            ),
        ));
        
        $response = curl_exec($curl);
        
        curl_close($curl);
        
        $data = json_decode($response);

        // 創建一個新的卡片資料
        $payment = new payments();

        // 設置欄位的值
        $payment->user_id = $user->id;
        $payment->payment_method = "LinePay";
        $payment->payment_currency = $qyery['currency'];
        $payment->status = "In Progress";
        $payment->payment_amount = $qyery['amount'];
        // 繼續設置其他欄位...

        // 儲存資料到資料庫
        $payment->save();

        // header("Location: ".$data->info->paymentUrl->web);
        return $data->info->paymentUrl->web;
        // return [
        //     'success' => false,
        //     'message' => $data
        // ];
    }

    public function confirm(Request $request) {
        $transactionId = $request->query('transactionId');
        $orderId = $request->query('orderId');

        $payment = payments::where('id', $orderId)->first();

        $sandBox = 'https://sandbox-api-pay.line.me';
        $uri = '/v3/payments/'.$transactionId.'/confirm';
        $channelId = '2004609569';
        $channelSecret = '465f27ae2239db90cd82824ec66e2498';
        $Nonce = date('c') . uniqid('-');
        $isSandbox = false;

        $qyery = [
            'amount' => $payment->payment_amount,
            'currency' => $payment->payment_currency
        ];

        $authMacText = $channelSecret . $uri . json_encode($qyery) . $Nonce;
        $Authorization = base64_encode(hash_hmac('sha256', $authMacText, $channelSecret, true));

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $sandBox.$uri,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>json_encode($qyery),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'X-LINE-ChannelId: '.$channelId,
                'X-LINE-Authorization-Nonce: '.$Nonce,
                'X-LINE-Authorization: '.$Authorization
            ),
        ));

        $response = curl_exec($curl);

        
        curl_close($curl);


        $responseArray = json_decode($response, true);

        // if ($responseArray === null) {
        //     echo "JSON 解析失敗";
        // } else {

        //     if (isset($responseArray['info']) && isset($responseArray['info']['payInfo'])) {
        //         // 訪問 'payInfo' 鍵的值
        //         $amount = $responseArray['info']['payInfo'][0]['amount'];
        //         // 繼續使用 $playInfo 的值
        //     } else {
        //         // 如果某些鍵丟失，可以進行錯誤處理
        //         echo "缺少 'info' 或 'payInfo' 鍵";
        //     }
        // }

        // 找到要修改的記錄
        $payment = payments::find($orderId);
        // 更新資料
        if($responseArray['returnCode']== "0000")
        {
            $payment->status = 'COMPLETED';
        }else
        {
            $payment->status = 'FAILED';

        }
        $payment->save();


        return[
            $responseArray
        ];
    }

    public function clickCard(Request $request){
        $user = Auth::user();

        if($user == null)
        {
            return[
                'success' => false,
                'message' => "No Login"
            ];
        }
        if($user->download_time <= 0 && $user->bonus_times <=0)
        {
            return[
                'success' => false,
                'message' => "No download time"
            ];
        }
       
        if($user->download_time <= 0)
        {
            $user->download_time -=1;
        }else
        {
            $user->bonus_times -=1;
        }

        $user->save();

        return[   
            'success' => true,
            'message' => [
                "download_time"=>  $user->download_time
            ]
        ];
    }

    public function getMMMM(Request $request) {
        $validator = Validator::make($request->all(),[
            'account' => 'required',
            'password' => 'required'
        ]);
        

        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => __('auth.failed'),
                'errors' => $validator->errors()->toArray()
            ];
        }

        $credentials = $request->only('account', 'password');

        if ($result = Auth::attempt($credentials)) {
            $auth = Auth::user();

        $BC_cards = cards::where('user_id', $user->id)->get();

        $getAllBC_Result = [
            'bearerToken_requery' => true,
            'input_param' => [
                
            ],
            "success" => [
                'success' => true,
                'message' => []
            ],
            "failed" => [
                'success' => false,
                'message' => "getAllBC error"
            ],
        ];

        foreach ($BC_cards as $BC_card) {
            $newData  = $BC_card->email;
            $name = $BC_card->name;
            
            $newData = [
                "id"   => $BC_card->id,
                "name" => $BC_card->edit_name,
                "releaseName" => $BC_card->release_name,
                "update_at" => $BC_card->update_at,
                "create_at" => $BC_card->create_at,
                "downloadTimes" => $BC_card->download_time
            ];
            array_push($getAllBC_Result['success']['message'], $newData);
        }
        return [
            $getAllBC_Result['success']
        ];

            return [
                'success' => true,
                'message' => [
                    'id' =>  $auth->id,
                    'name' =>  $auth->name,
                    'email' =>  $auth->email
                ]
            ];
        } else {
            return [
                'success' => false,
                'message' => __('auth.failed'),
                'errors' => $result
            ];
        }

    }

    public function resetPassword(Request $request) {

        $resetPasswordToken = $request->query('resetPasswordToken');
        $email = $request->query('email');

        if ($user = User::where('email', $email)->first()) {
            if ($user->resetPasswordToken == $resetPasswordToken)
            {
                return '<script>window.location = "http://192.168.0.112:5173/ForgetPassword/?resetPasswordToken='.$user->resetPasswordToken.'&email='.$email.'";</script>';
                // return '<script>window.location = "https://4dbox.lightmatrix3d.com/?resetPasswordToken='.$user->resetPasswordToken.'&email='.$email.'";</script>';
            }
            else
            {                
                return '<script>window.location = "http://192.168.0.112:5173/ForgetPassword/";</script>';
                // token 失效
            }
        }
    }

    /**
     * confirm mail
     */
    public function registerMember($code) {

        if ($user = User::where('confirm_code', $code)->first()) {

            if (now() > $user->confirm_code_expired_at) {
                return '<script>window.location = "http://192.168.0.112:5173/?memberRegistResult=expiredVerificationCode";</script>';
                // return '<script>window.location = "https://4dbox.lightmatrix3d.com/?memberRegistResult=expiredVerificationCode";</script>';
                return [
                    'success' => false,
                    'message' => 'expiredVerificationCode'
                ];
            }

            $user->confirm_code = null;
            $user->confirm_code_expired_at = null;
            $user->email_auth = true;
            $user->email_verified_at = now();

            $user->save();

            return '<script>window.location = "http://192.168.0.112:5173/?memberRegistResult=registerSuccess";</script>';
            // return '<script>window.location = "https://4dbox.lightmatrix3d.com/?memberRegistResult=success";</script>';
            return [
                'success' => true,
                'message' => '註冊成功！'
            ];
        };

        return '<script>window.location = "http://192.168.0.112:5173/?memberRegistResult=incorrectVerificationCode";</script>';
        // return '<script>window.location = "https://4dbox.lightmatrix3d.com/?memberRegistResult=incorrectVerificationCode";</script>';
        return [
            'success' => false,
            'message' => 'incorrectVerificationCode'
        ];
    }

    /**
     * update user
     */
    public function userUpdate(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'phone' => 'nullable|regex:/^09\d{2}-?\d{3}-?\d{3}$/', //手機號碼
            'password' => ['nullable', 'confirmed', 'min:8'],
            'old_password' => 'nullable|required_with:password|current_password'
        ]);

        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => 'updateUserDataFailed',
                'errors' => $validator->errors()->toArray()
            ];
        }

        $user = Auth::user();
        $user->name = $request->name;

        if ($request->phone) {
            $user->phone = $request->phone;
        }

        if ($request->password) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return [
            'success' => true,
            'message' => 'update user success!',
        ];
    }

    /**
     * update member name
     */
    public function updateMemberName(Request $request) {

        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
        ]);

        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => 'updateUsernameFailed',
                'errors' => $validator->errors()->toArray()
            ];
        }

        $user = Auth::user();
        $user->name = $request->name;
        $user->save();

        return [
            'success' => true,
            'message' => 'update member name success!'
        ];
    }

    /**
     * update password
     */
    public function updatePassword(Request $request) {

        $validator = Validator::make($request->all(), [
            'password' => 'required|password|confirmed',
            'old_password' => 'required|current_password'
        ]);

        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => 'updatePasswordFailed',
                'errors' => $validator->errors()->toArray()
            ];
        }

        $user = Auth::user();
        $user->password = Hash::make($request->password);
        $user->save();

        return [
            'success' => true,
            'message' => 'update password success!'
        ];
    }

    public function changePassword(Request $request) {

        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'email' => 'required|email',
            'newPassword' => 'required',
        ]);

        if (!$request->token)
            abort(415);
        
        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => __('register.failed'),
                'errors' => $validator->errors()->toArray()
            ];
        }

        $resetPasswordToken = $request->token;
        $email = $request->email;
        $user = User::where('email', $email)->first();

        if ($user) {
            if ($user->resetPasswordToken == $resetPasswordToken)
                $user->password = Hash::make($request->newPassword);
            else
            {
                return [
                    'success' => false,
                    'message' => "TokenExpired"
                ];
            }

            $user->save();
        }
        else {
            return [
                'success' => false,
                'message' => "UserNotFound"
            ];
        }

        return [
            'success' => true,
            'message' => "ChangeSuccess"
        ];
    }

    /**
     * query order list
     */
    public function queryOrderList() {
        if (Auth::id() == 1)
        {
            $query = Order::where(function ($query) {
                $query
                ->where('user_id', Auth::id())
                ->Where('type', 0);
            })
            ->orWhere(function ($query) {
                $query
                ->where('user_id', Auth::id())
                ->Where('type', 1)
                ->whereHas('product_solution_order', function($orderQuery) {
                    $orderQuery->where('is_activated', 1);
                });
            });
        }
        else
        {
            $query = Order::where(function ($query) {
                $query
                ->where('user_id', Auth::id())
                ->WhereDoesntHave('product_solution_order');
            })
            ->orWhere(function ($query) {
                $query
                ->where('user_id', Auth::id())
                ->whereHas('product_solution_order', function($orderQuery) {
                    $orderQuery->where('is_activated', 1);
                });
            });
        }


        return [
            'success' => true,
            'message' => new OrderCollection($query->latest()->paginate(999999)),
        ];
    }

    /**
     * update video name
     */
    public function updateVideoName(Request $request,$id) {

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'id' => 'required|exists:media,id'
        ]);

        if ($video = Media::find($id)) {
            $video->name = $request->name;
            $video->save();
        };

        return [
            'success' => true,
            'message' => 'update video name success!'
        ];
    }

    /**
     * upload video
     */
    public function uploadVideo(Request $request) {
        try{
            $validator = Validator::make($request->all(), [
                'video' => 'required|mimes:mp4,mov,ogg,qt|max:1048576', // aaaa
            ]);

            if ($validator->fails()) {
                return [
                    'success' => false,
                    'message' => 'uploadVideoFailed',
                    'errors' => $validator->errors()->toArray()
                ];
            }

            $cpuUsage = $this->get_cpu_usage();

            if ($cpuUsage>7) {
                return[
                    'success' => false,
                    'message' => 'systemBusy',
                    'cpu' => $cpuUsage,
                ];
            }

            //create new order, media and store file to storage
            $repository = new OrderRepository();
            $repository->userUploadVideo($request);

            $media = $repository->getMedia();

            event(new PicUploaded($media));

            return [
                'success' => true,
                'message' => 'upload video success!',
                'cpu' => $cpuUsage,
            ];
        }
        catch(e) {
            return [
                'success' => false,
                'message' => e.message,
            ];
        }
    }

    /**
     * upload picture
     */
    public function uploadPicture(Request $request) {
        try{
            $validator = Validator::make($request->all(), [
                'pic' => 'required|image|mimes:jpeg,png,jpg,gif,svg,bmp,webp|max:20000',
            ]);

            if ($validator->fails()) {
                return [
                    'success' => false,
                    'message' => 'uploadImageFailed',
                    'errors' => $validator->errors()->toArray()
                ];
            }

            $cpuUsage = $this->get_cpu_usage();

            if ($cpuUsage > 7) {
                return[
                    'success' => false,
                    'message' => 'systemBusy',
                    'cpu' => $cpuUsage,
                ];
            }

            //create new order, media and store file to storage
            $repository = new OrderRepository();
            $repository->userUploadMedia($request);

            $media = $repository->getMedia();

            event(new PicUploaded($media));

            return [
                'success' => true,
                'message' => 'upload picture success!',
                'cpu' => $cpuUsage,
            ];
        }
        catch(e) {
            return [
                'success' => false,
                'message' => e.message,
            ];
        }
    }

    /**
     * upload canvas picture
     */
    public function uploadCanvas(Request $request) {
        try{        
            // $validator = Validator::make($request->all(), [
            //     'pic' => 'required', // |string
            // ]);
            $validator = Validator::make($request->all(), [
                'pic' => 'required_without:picFile',
                'picFile' => 'required_without:pic',
            ]);

            if ($validator->fails()) {
                return [
                    'success' => false,
                    'message' => 'uploadImageFailed',
                    'errors' => $validator->errors()->toArray()
                ];
            }

            $cpuUsage = $this->get_cpu_usage();

            if ($cpuUsage > 50) {
                return[
                    'success' => false,
                    'message' => 'systemBusy',
                    'cpu' => $cpuUsage,
                ];
            }

            // =====

            //create new order, media and store file to storage
            $repository = new OrderRepository();
            // $repository->userUploadMediaFromCanvas($request);

            if ($request->picFile)
                $repository->userUploadMediaFromFile($request);
            else
                $repository->userUploadMediaFromCanvas($request);

            $media = $repository->getMedia();
            
            $user = Auth::user();

            $target = 'points';
            if ($request->to) {
                $target = $request->to;
            }
            
            //create new order, media and store file to storage
            if ($user->$target<-(int)$request->value) {
                $repository->userAddValueFailed($request);

                return [
                    'success' => false,
                    'message' => [
                        'type' => 'not enough points. Please add value !',
                    ]
                ];
            }

            // if succ, then add value
            $user->$target += (int)$request->value;
            $user->save();

            if ($media->user_id == 598)
            {
                // || $media->user_id == 1
                AutoDeleteGuestMedia::dispatch($media->id)->delay(now()->addMinutes(10));
                // AutoDeleteGuestMedia::dispatch($media->id)->delay(now()->addSeconds(30));
            }

            event(new PicUploaded($media));

            return [
                'success' => true,
                'message' => 'upload picture success! media id: '.$media->id,
                'cpu' => $cpuUsage
            ];
        }
        catch(e) {
            return [
                'success' => false,
                'message' => e.message,
            ];
        }
    }

    /**
     * get user videos
     */
    public function videos(Request $request) {

        $validator = Validator::make($request->all(),[
            'page' => 'nullable|integer',
            'type' => ['nullable', 'regex:/^([0-9]+|all)$/'],
        ]);

        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => $validator->messages()->first(),
                'errors' => $validator->errors()->toArray()
            ];
        }

        $query = Media::where('user_id', Auth::id());

        $type_check = $request->type || $request->type === 0 || $request->type === '0';
        if ($type_check && $request->type != 'all') {
            $query->whereHas('order', function($orderQuery) use ($request) {
                $orderQuery->where('type', $request->type);
            });
        }

        return [
            'success' => true,
            'message' => new MediaCollection($query->paginate(10)),
          /*   'sql' => $query->toSql(),
            'bindings' => $query->getBindings() */
        ];
    }

    /**
     * get user orders
     */
    public function orders(Request $request) {

        $validator = Validator::make($request->all(), [
            'page' => 'nullable|integer',
            'type' => ['nullable', 'regex:/^([0-9]+|all)$/'],
        ]);

        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => $validator->messages()->first(),
                'errors' => $validator->errors()->toArray()
            ];
        }

        $query = Order::where('user_id', Auth::id())->orderBy('created_at', 'desc');

        if ($request->dt_condition) 
        {
            $dt_condition = Carbon::now()->subDays($request->dt_condition)->toDateString();
            $query->whereDate('created_at', '>=', $dt_condition);
        }
        
        $type_check = $request->order_type || $request->order_type === 0 || $request->order_type === '0';
        if ($type_check && $request->order_type != 'all')
        {
            $query->where('type', $request->order_type); 
        }


        if ($request->activeFilter)
        {
            if ($request->order_type === "1" || $request->order_type === 1) 
            {
                $query->whereHas('product_solution_order', function($orderQuery) {
                    $orderQuery->where('is_activated', 1);
                });
            }

            if ($request->order_type == 'all') 
            {
                $query->where(function ($orderQuery) {
                    $orderQuery->whereHas('product_solution_order', function ($pQuery) {
                        $pQuery
                        ->where('type', 1)
                        ->where('is_activated', 1);
                    })->orWhere(function ($pQuery) {
                        $pQuery
                        ->where('type', 0);
                    });
                });
            }
        }

        if ($request->hasImg) 
        {
            $query->where(function ($tmp) {
                $tmp
                ->where('type', 0)
                ->whereHas('media', function ($m_query) {
                    // $m_query->where('status', "!=", 3);
                    // ->where('status', '!=', 2);
                    $m_query->whereNotIn('status', [2, 3]);
                })
                ->orWhere('type', 1);
            });
            
            // $filteredResults = $results->filter(function ($item) {
            //     // 假设 `media` 是加载了的关系，并且 `cover` 是存储在 S3 上的文件路径
            //     if ($item->media && Storage::disk('s3')->exists($item->media->cover)) {
            //         return true;
            //     }
            //     return false;
            // });
        }
        
        return [
            'success' => true,
            'message' => new OrderCollection($query->paginate(30)),
            'request_type' => $request->order_type
/*             'sql' => $query->toSql(),
            'bindings' => $query->getBindings(),
            'condition' =>($request->order_type || $request->order_type === 0 || $request->order_type==='0') && $request->order_type != 'all' */
        ];
    }


    public function notifications(Request $request) {
        $dt_condition = Carbon::now();
        
        $query = Notification::where(function ($query) use ($dt_condition) {
            $query
            ->where('user_id', Auth::id())
            ->where('release_at', '<=', $dt_condition); 
        })
        ->orWhere(function ($query) use ($dt_condition) {
            $query
            ->whereNull('user_id')
            ->where('release_at', '<=', $dt_condition); 
        });

        $query = $query->where('is_activated', 1)->get();

        return [
            'success' => true,
            'message' => $query
        ];
    }

    /**
     * get projects
     */
    public function projects(Request $request) {
        return [
            'success' => true,
            'message' => Project::get(),
        ];
    }

     /**
     * get products
     */
    public function products(Request $request) {

        $validator = Validator::make($request->all(), [
            'page' => 'nullable|integer',
            'type' => ['nullable', 'regex:/^([0-9]+|all)$/'],
        ]);

        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => $validator->messages()->first(),
                'errors' => $validator->errors()->toArray()
            ];
        }

        
        $query = Product_solution::select('product_id')->where('is_activated', 1)
        ->groupBy('product_id')
        ->with('product', function ($child_query) {
            $child_query
            ->where('type', 0)
            ->orWhere('type', 1)->has('album.albumDetail');
        })->get()->filter(function ($item) {
            return $item->product !== null;
        })->pluck('product'); 
        
        // $query = Product::get();
        //'message' => new OrderCollection ($query->paginate(10)),
        return [
            'success' => true,
            'message' => new ProductCollection($query),
/*             'sql' => $query->toSql(),
            'bindings' => $query->getBindings(),
            'condition' =>($request->order_type || $request->order_type === 0 || $request->order_type==='0') && $request->order_type != 'all' */
        ];
    }

     /**
     * get stores
     */
    public function stores(Request $request) {

        $validator = Validator::make($request->all(), [
            'page' => 'nullable|integer',
            'type' => ['nullable', 'regex:/^([0-9]+|all)$/'],
        ]);

        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => $validator->messages()->first(),
                'errors' => $validator->errors()->toArray()
            ];
        }

        $query = Store::get();
        //'message' => new OrderCollection ($query->paginate(10)),
        return [
            'success' => true,
            'message' => new StoreCollection($query), // new ProductCollection ($query)
/*             'sql' => $query->toSql(),
            'bindings' => $query->getBindings(),
            'condition' =>($request->order_type || $request->order_type === 0 || $request->order_type==='0') && $request->order_type != 'all' */
        ];
    }

    /**
     * get albums
     */
    public function albums(Request $request) {

        $validator = Validator::make($request->all(), [
            'page' => 'nullable|integer',
            'type' => ['nullable', 'regex:/^([0-9]+|all)$/'],
        ]);

        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => $validator->messages()->first(),
                'errors' => $validator->errors()->toArray()
            ];
        }

        $query = Album::where('user_id', Auth::id())->get();

        return [
            'success' => true,
            'message' => new AlbumCollection($query)
            
        ];
    }

    public function product_solutions(Request $request) {

        $validator = Validator::make($request->all(), [
            'page' => 'nullable|integer',
            'type' => ['nullable', 'regex:/^([0-9]+|all)$/'],
        ]);

        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => $validator->messages()->first(),
                'errors' => $validator->errors()->toArray()
            ];
        }

        $query = Product_solution::where('product_id', $request->productID)->where('is_activated', 1);

        return [
            'success' => true,
            'message' => new ProductSolutionCollection($query->paginate(10)),
        ];
    }

    public function plan_solutions(Request $request) {
        $query = Plan_solution::where('is_activated', 1);
        return [
            'success' => true,
            'message' => $query->get(),
        ];
    }

    public function get2Dpics() {
        define("TYPE_PIC", 1);
        define("STATUS_PROCESSING", 0);

        $videos = Media::where('type', 1)->where('status', 0)->whereNotNull('original')->where('is_staff_uploaded',0)->get();
        $pics = [];
        foreach($videos as $video) {
            $pics[] = (object)['id' => $video->id,
            'name' => $video->name,
            'obj' =>Storage::disk('s3')->temporaryUrl($video->original??$video->obj, now()->addHour()),
            'path' => (new OrderRepository($video->order))->getPath($video->id)];
        }

        // crop 版本
        // $videos = Media::where('type', TYPE_PIC)->where('status', STATUS_PROCESSING)->whereNotNull('crop')->where('is_staff_uploaded', 0)->get();        
        // $pics = [];
        // foreach ($videos as $video) {
        //     $pics[] = (object)['id' => $video->id,
        //     'name' => $video->name,
        //     'obj' =>Storage::disk('s3')->temporaryUrl($video->crop??$video->obj, now()->addHour()),
        //     'path' => (new OrderRepository($video->order))->getPath($video->id)];
        // }


        return [
            'success' => true,
            'message' => $pics,
        ];
    }

    public function mediaChangeStatus($media) {
        define("TYPE_VID", 0);
        define("TYPE_PIC", 1);

        $repo = new OrderRepository($media->order);
        $media->status = 1;
        
        if ($media->type == TYPE_PIC) {
            $media->obj = $repo->getPath($media->id);
        }

        if ($media->type == TYPE_VID) {
            $media->obj = $repo->getVideoPath($media->id);

            //if media is created by staff, add cover
            if ($media->is_staff_uploaded == 1) {
                $media->cover = $repo->getVideoCoverPath($media->id);
            }
        }

        $media->finish_time = now();
        $media->save();

        if ($media->type == TYPE_VID) {
            event(new CompleteTransformVideo($media));
        }

        if ($media->type == TYPE_PIC) {
            event(new CompleteTransformPic($media));
        }
    }

    public function set2DpicFinish(Request $request) {

        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:media,id',
        ]);

        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => 'mediaNotFound',
                'errors' => $validator->errors()->toArray()
            ];
        }

        $media = Media::where('id', $request->id)->first();
        if ($media) {
            $this->mediaChangeStatus($media);
        }

        return [
            'success' => true
        ];
    }

    public function deleteVideo(Request $request, Media $media) {
        define("STATUS_DELETED", 3);

        if ($request->user()->cannot('update', $media)) {
            abort(403);
        }

        $media->status = STATUS_DELETED;

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

        $media->original = null;
        $media->obj = null;
        $media->cover = null;

        $media->save();

        return [
            'success' => true
        ];
    }

    public function videoFailed(Request $request, Media $media) {
        define("STATUS_FAILED", 2);

        if ($request->user()->cannot('update', $media)) {
            abort(403);
        }

        $media->status = STATUS_FAILED;
        $media->save();

        event(new PicUploadFailed($media));

        return [
            'success' => true
        ];
    }

    public function getVideos() {
        $media = Media::where('type', 0)->where('status', 0)->whereNotNull('original')->where('is_staff_uploaded',0)->get();
        $videos = [];
        foreach ($media as $medium) {
            $videos[] = (object)['id' => $medium->id,
            'name' => $medium->name,
            'original' => Storage::disk('s3')->temporaryUrl($medium->original, now()->addHour()),
            'path' => (new OrderRepository($medium->order))->getVideoPath($medium->id)];
        }
        return [
            'success' => true,
            'message' => $videos,
        ];
    }

    public function setVideoFinish(Request $request) {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:media,id',
        ]);

        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => 'mediaNotFound',
                'errors' => $validator->errors()->toArray()
            ];
        }

        $media = Media::where('id', $request->id)->first();
        if ($media) {
            $this->mediaChangeStatus($media);
        }
        return [
            'success' => true
        ];
    }

    public function video(Request $request, Media $media) {
        try{
            $userOrders = Order::where('user_id', $request->user()->id)->where('type', '1')->get();

            $media_arr = [];
            $result_arr = [];
            $albumCollection = null;
            $tmp_media = null;

            $can_view = false;
            
            if ($request->user()->cannot('view', $media)) {
                foreach ($userOrders as $order) {
                    if ($order->product_solution_order == null) {
                        continue;
                    }

                    
                    if ($order->product_solution_order->product_solution->product->type == 0) {
                        $tmp_media = [$order->product_solution_order->product_solution->product->media];
                    }
                    else {
                        $album = collect([$order->product_solution_order->product_solution->product->album]);
                        $albumCollection = new AlbumCollection($album);
                        $tmp_media = $albumCollection->first()->media;
                    }
                    
                    array_push($media_arr, $tmp_media);
                }
                
                foreach ($media_arr as $temp_arr) {
                    foreach ($temp_arr as $temp) {
                        if ($temp->id == $media->id) {
                            $can_view = true;
                        }
                    }
                }

                if (!$can_view) {
                    abort(403);
                }
            }

            return [
                'success' => true,
                'message' => [
                    'cover' => Storage::disk('s3')->temporaryUrl($media->cover, now()->addHour()),
                    'obj' => Storage::disk('s3')->temporaryUrl($media->obj, now()->addHour()),
                ],
            ];
        }
        catch(Exception $e) {
            return var_dump($e);
        }
    }

    public function test(Request $request) {
        // return [
        //     'success' => true,
        //     'message' =>Auth::id(),
        // ];

        $query = Order::where(function ($query) use ($request) {
            $query
            ->where('user_id', Auth::id())
            ->WhereDoesntHave('product_solution_order');
        })
        ->orWhere(function ($query) use ($request) {
            $query
            ->where('user_id', Auth::id())
            ->whereHas('product_solution_order', function($orderQuery) {
                $orderQuery->where('is_activated', 1);
            });
        });
        $res = clone $query;
        
        return [
            'success' => true,
            'message' => $res->toSql()
        ];
        // $dt_condition = Carbon::now()->toDateString();

        // $query = Notification::where(function ($query) use ($dt_condition) {
        //     $query->where('user_id', Auth::id())
        //           ->whereDate('release_at', '<=', $dt_condition);
        // })
        // ->orWhere(function ($query) use ($dt_condition) {
        //     $query->whereNull('user_id')
        //           ->whereDate('release_at', '<=', $dt_condition);
        // });

        // return [
        //     'success' => true,
        //     'message' => $query->get(),
        // ];
    }

    /**
     * get projects
     */
    public function checkPaymentFlow(Request $request) {
        // check programming error
        /* $validator = Validator::make($request->all(),[
            'value' => 'nullable', //|integer
            'type' => ['nullable', 'regex:/^([0-9]+|all)$/'],
        ]);

        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => $validator->messages()->first(),
                'errors' => $validator->errors()->toArray()
            ];
        } */

        $user = Auth::user();

        $target = 'points';
       
        if ($request->to) {
            $target = $request->to;
        }

        // check action type
        switch ($request->type) {
            // check 2to3 available
            case 0:
                //create new order, media and store file to storage
                if ($user->$target<-(int)$request->value) {
                    return [
                        'success' => false,
                        'message' => [
                            'type' => 'not enough points. Please add value !',
                        ]
                    ];
                }
            
            // check buy items
            case 1:
                $repository = new OrderRepository();
                $repository->createOrder($request);

                if ($user->$target<-(int)$request->value) {
                    return [
                        'success' => false,
                        'message' => [
                            'type' => 'not enough points to buy. Please add value !',
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

                if (!$request->from) {
                    break;
                }

                if ($request->from == 'ads') {
                    $user->ads_times += 1;
                }
                
                break;
            
            case 3:
                

                
                break;
            
            default:
                return [
                    'success' => false,
                    'message' => [
                        'type' => 'ain\'t regular type',
                    ]
                ];
        }

        // if succ, then add value
        $user->$target+=(int)$request->value;
        $user->save();

        return [
            'success' => true,
            'message' => [
                'points' => $user->points,
                'free_points' => $user->free_points,
            ]
/*           'sql' => $query->toSql(),
            'bindings' => $query->getBindings(),
            'condition' =>($request->order_type || $request->order_type === 0 || $request->order_type==='0') && $request->order_type != 'all' */
        ];
    }


    /**
     * create album
     */
    public function albumCreate(Request $request) {

        $validator = Validator::make($request->all(), [
            'name' => 'required',
        ]);

        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => __('register.failed'),
                'errors' => $validator->errors()->toArray()
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

    public function deleteAlbum(Request $request) {
        $albumID = $request->album['id'];
        $productsWithoutAlbum = !Product::whereHas('album', function ($query) use ($albumID) {
            $query->where('album_id', $albumID);
        })->exists();

        if (!$productsWithoutAlbum) {
            return [
                'success' => false,
                'message' => 'albumInProduct'
            ];
        }

        $album = Album::where('id', $request->album['id'])->first();
        $album->delete();

        // $album->save();
        
        return [
            'success' => true,
            'message' => [
                'id' =>  ''
            ]
        ];
    }

    /**
     * add media to album
     */
    public function editToAlbum(Request $request) {

        /* $validator = Validator::make($request->all(),[
            'name' => 'required',
        ]);

        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => __('register.failed'),
                'errors' => $validator->errors()->toArray()
            ];
        } */

        $albumID = $request->album['id'];
        $productsWithoutAlbum = !Product::whereHas('album', function ($query) use ($albumID) {
            $query->where('album_id', $albumID);
        })->exists();

        if (!$productsWithoutAlbum) {
            return [
                'success' => false,
                'message' => 'albumInProduct'
            ];
        }

        $album = Album::where('id', $request->album['id'])->first();
        $request_media = $request->chosenMedia;

        if ($request->editType == 'add') {
            foreach ($request->chosenMedia as $key => $media) {
                $albumDetail = AlbumDetail::where('album_id', $request->album['id'])->where('media_id', $media['id']);
                if (!$albumDetail->exists())
                {
                    AlbumDetail::create([
                        'album_id' => $request->album['id'],
                        'media_id' => $media['id']
                    ]);
                }
                // $media = Media::where('id', $media['id'])->first();
                // $media->album()->associate($album);
                // $media->save();
            }    
        }
        else if ($request->editType == 'delete') {
            foreach ($request->chosenMedia as $key => $media) {
                $albumDetail = AlbumDetail::where('album_id', $request->album['id'])->where('media_id', $media['id']);
                if ($albumDetail->exists())
                    $albumDetail->delete();
                // $media = Media::where('id', $value['id'])->first();
                // $media->album_id = null;
                // $media->save();
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
     * subscribe a product
     */
    public function product_subscribe(Request $request) {
        $validator = Validator::make($request->all(), [
            'product_solution' => 'nullable', // |integer
            'type' => ['nullable', 'regex:/^([0-9]+|all)$/'],
        ]);

        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => $validator->messages()->first(),
                'errors' => $validator->errors()->toArray()
            ];
        }

        $user = Auth::user();
                
        if ($request->product_solution["isFree"])
        {
            // 找該 plan 內的0元方案
            $FreePlan = Product_solution::where('product_id', $request->product_solution["id"])->Where('costs', 0)->first();
            
            $copy = clone $FreePlan;
            foreach ($copy as $key => $value) {
                $request->request->add([$key => $value]);
            }

            $product_solution = $FreePlan;
            $product = $product_solution->product;
            $product_solution_orders = $product_solution->product_solution_order;
        }
        else
        {
            // $have_order = Product_solution_order::where('email', 'example@example.com')->exists();
            $product_solution = Product_solution::where('id', $request->product_solution['id'])->first();
            $product = $product_solution->product;
            $product_solution_orders = $product_solution->product_solution_order;
        }

        // Prevent subscribe self.
        if ($product->type == 0) {
            $media_list = [$product->media];
        }
        else{
            $album = collect([$product->album]);
            $albumCollection = new AlbumCollection($album);
            $media_list = $albumCollection->first()->media;
        }

        foreach($media_list as $media) {
            if ($media->order->user->id == $user->id) {
                return [
                    'success' => false,
                    'message' => 'yourPhoto',
                ];
            }
        }

        // Prevent subscribe same product.
        foreach($product_solution_orders as $product_solution_order) {
            if ($product_solution_order->order->user->id == $user->id && $product_solution_order->status == 0 && $product_solution_order->is_activated != 0) {
                return [
                    'success' => false,
                    'message' => 'alreadySubscribed',
                ];
            }
        }

        // if succ, then make an order and make a solution order
        $repository = new OrderRepository();
        if ($request->product_solution["isFree"])
            $order_detail = $repository->subscribeProductFree($request, $product_solution);
        else 
        {
            $order_detail = $repository->subscribeProduct($request);

            $payment = Payment::where('transaction_id', $request->resource_id)->first();
            $payment->order_id = $order_detail->solution_order->order_id;
            $payment->save();
        }

        // $media = $repository->getMedia();
        // foreach($media_list as $media) {
        //     event(new PicUploaded($media));
        // }

        event(new AIBoxRefresh($user->id));
        // if ($user->id == 1) {
            // Log::info('ID: '.$order_detail->solution_order->order_id);
            // ProductUnsubscribe::dispatch($order_detail->solution_order->order_id)->delay(now()->addMinutes(1));
            // ProductUnsubscribe::dispatch($order_detail->solution_order->order_id)->delay(now()->addSeconds(20));
        // }
        // ====== check paypal trasaction status =======

        if ($request->product_solution["isFree"]){
            return [
                'success' => true,
                'message' => [
                    'order' => $order_detail
                ]
            ];
        }
        else {
            return [
                'success' => true,
                'message' => [
                    'order' => $order_detail,
                    'payment' => $request->resource_id,
                ]
            ];
        }
    }

    /**
     * subscribe a product
     */
    public function plan_subscribe(Request $request) {
        // check programming error
        $validator = Validator::make($request->all(), [
            'product_solution' => 'nullable', // |integer
            'type' => ['nullable', 'regex:/^([0-9]+|all)$/'],
        ]);

        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => $validator->messages()->first(),
                'errors' => $validator->errors()->toArray()
            ];
        }

        $user = Auth::user();
        
        // if succ, then make an order and make a solution order
        $repository = new OrderRepository();
        $order_detail = $repository->subscribePlan($request);

        $payment = Payment::where('transaction_id', $request->resource_id)->first();
        $payment->order_id = $order_detail->solution_order->order_id;
        $payment->save();
        
        // ====== check paypal trasaction status =======

        return [
            'success' => true,
            'message' => [
                'order' => $order_detail,
                'payment' => $request->resource_id,
            ]
        ];
    }
    
    /**
     * unsubscribe a product
     */
    public function product_unsubscribe(Request $request) {
        // check programming error
        $validator = Validator::make($request->all(),[
            'order' => 'nullable', // |integer
            'type' => ['nullable', 'regex:/^([0-9]+|all)$/'],
        ]);

        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => $validator->messages()->first(),
                'errors' => $validator->errors()->toArray()
            ];
        }

        $user = Auth::user();

        // $have_order = Product_solution_order::where('email', 'example@example.com')->exists();

        // Cancel mark when order variable is an Order object
        /* $order = Order::where('id', $request->order['id']=)->first(); */

        $order = Order::where('id', $request->order)->first();
        $product_solution_order = $order->product_solution_order;

        if (!$order->product_solution_order) {
            return [
                'success' => false,
                'message' => 'noOrder',
            ];
        }

        $product_solution_order->is_activated = 0;
        $product_solution_order->save();

        event(new AIBoxRefresh($order->user_id));
        
        return [
            'success' => true,
            'message' => 'unsubscribeSuccess'
        ];
    }

    /**
     * create payment_detail to table
     */
    public function checkout_order_approved(Request $request) {
        $userid = Auth::user()->id;
        $details = $request->input('details');
        $transactionId = $request->input('resourceId');
        
        $order_data = Payment::create([
            'user_id' => $userid,
            // 'product_solution_id' => $request->input('projectId'),
            'order_id' => null,
            'payment_method' => "paypal",
            'event_type' => "CHECKOUT.ORDER.APPROVED",
            'payment_amount' => $request->input('payment_amount'),
            'payment_currency' => $request->input('payment_currency'),
            'transaction_id' => $transactionId,
            'status' => $request->input('capture_status'),
            'summary' => ""
        ]);      
        
        $jsonData = json_encode($details, JSON_PRETTY_PRINT);
        $SavePath = storage_path('PamentDetails');
        if (!file_exists($SavePath)) {
            mkdir($SavePath, 0775, true);
        }

        $filePath = $SavePath . '/' .$transactionId . '-CHECKOUT_ORDER.json';
        file_put_contents($filePath, $jsonData);
        
        return [
            'success' => true,/* 
            'message' => [
                'id' => "asdfsd"
            ] */
        ];
        // return response()->json('success');
    }

    public function getUserUsage(Request $request) {
        $user_id = Auth::id();

        // 0 vid
        $vid_usage = Media::where('user_id', $user_id)->where('type', 0)->count();
        // 1 pic
        $pic_usage = Media::where('user_id', $user_id)->where('type', 1)->count();
        
        return [
            'success' => true,
            'message' => [
                'vid_usage' => $vid_usage,
                'pic_usage' => $pic_usage
            ]
        ];
    }

    public function getCurrentPlan(Request $request) {
        $user_id = Auth::id();

        $user_solution_order = Order::where('user_id', Auth::id())->where('type', 4)->whereHas('plan_solution_order', function($orderQuery) use ($request) {
            $orderQuery->where('is_activated', 1);
        })->get();

        $last_solution = Plan_solution_order::where('is_activated', 0)->whereHas('order', function ($query) {
            $query->where('user_id', Auth::id())->where('type', 4);
        });

        if ($last_solution->exists()) 
            $last_solution = $last_solution->orderBy('expired_at', 'desc')->first();
        else
            $last_solution = null;

        $user_solution_list = array();
        if (count($user_solution_order) < 1)
        {
            array_push($user_solution_list, [
                'plan' =>Plan_solution::where('costs', 0)->first(),
                'order' => null,
                'last_plan' => $last_solution,
            ]);
            return [
                'success' => true,
                'message' => $user_solution_list
            ];
        }
        else {
            array_push($user_solution_list, [
                'plan' => $user_solution_order[0]->plan_solution_order->plan_solution,
                'order' => $user_solution_order[0]->plan_solution_order,
                'last_plan' => $last_solution
            ]);
        }
        
        return [
            'success' => true,
            'message' => $user_solution_list
        ];
    }

    /**
     * check if subscribed 
     */
    public function check_product_subscribe(Request $request) {
        // check programming error
        $validator = Validator::make($request->all(), [
            'product' => 'nullable', // |integer
            'type' => ['nullable', 'regex:/^([0-9]+|all)$/'],
        ]);

        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => $validator->messages()->first(),
                'errors' => $validator->errors()->toArray()
            ];
        }

        $user = Auth::user();
        
        // $have_order = Product_solution_order::where('email', 'example@example.com')->exists();
        $product = Product::where('id', $request->product['id'])->first();

        $product_solution = $product->product_solution;

        // Prevent subscribe self.
        if ($product->type == 0) {
            $media_list = [$product->media];
        }
        else {
            $album = collect([$product->album]);
            $albumCollection = new AlbumCollection($album);
            $media_list = $albumCollection->first()->media;
        }

        foreach ($media_list as $media) {
            if ($media->order->user->id == $user->id) {
                return [
                    'success' => false,
                    'message' => 'yourPhoto',
                ];
            }
        }

        // Prevent subscribe same product.
        foreach ($product_solution as $solution) {
            $product_solution_orders = $solution->product_solution_order;

            foreach ($product_solution_orders as $product_solution_order) {
                if ($product_solution_order->order->user->id == $user->id && $product_solution_order->status == 0) {
                    return [
                        'success' => false,
                        'message' => 'alreadySubscribed',
                    ];
                }
            }
        }

        return [
            'success' => true,
            'message' => "可以訂閱"
        ];
    }

    // public function throttleTest(Request $request) {
    //     return "AA";
    // }
}
