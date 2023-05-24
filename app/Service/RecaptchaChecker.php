<?php

namespace App\Service;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class RecaptchaChecker{

    protected $secret;
    protected $url;

    public function __construct()
    {
        $this->secret = env('RECAPTCHA_SECRET');
        $this->url = 'https://www.google.com/recaptcha/api/siteverify';
    }

    public function check($recaptcha = null){

        if(!$recaptcha){
            $recaptcha = request('recaptcha');
        }

        $res = Http::asForm()->post($this->url, [
            'secret' => $this->secret,
            'response' => $recaptcha,
            'remoteip' => request()->ip()
        ]);

        Log::info($recaptcha);
        $resObj = $res->object();
        Log::info($res->json());


        if($resObj->success == true){
            return true;
        }

        return false;

    }

}
