<?php

namespace App\Http\Controllers;

use App\Events\AddDevice;
use App\Http\Resources\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class UserController extends Controller
{
    //
    public function dashboard(){
        return view('dashboard', [
            'orders' => auth()->user()->orders()->get()
        ]);
    }

    public function profile(){
        $user = auth()->user();
        return [
            'id' =>$user->id,
            'name' =>$user->name,
            'email'=>$user->email,
            'phone'=>$user->phone,
            'devices'=>$user->devices,
        ];
    }

    public function addDevice(){
        $user = auth()->user();
        event(new AddDevice($user, request('deviceId')));
        Log::info('Device added', ['user'=>$user->id, 'device'=>request('deviceId')]);
        return [     'success'=>true,
        'message'=>'Device added',];
    }


}
