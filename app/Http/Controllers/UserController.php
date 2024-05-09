<?php

namespace App\Http\Controllers;

use App\Models\Pin;
use App\Models\User;
use App\Events\AddDevice;
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
            // 'devices'=>$user->devices,
            'points'=>$user->points,
            'freePoints'=>$user->free_points,
            'VIP'=>$user->VIP,
            'adsTimes'=>$user->ads_times,
        ];
    }

    public function addDevice(){
        $user = auth()->user();
        event(new AddDevice($user, request('deviceId')));
        Log::info('Device added', ['user'=>$user->id, 'device'=>request('deviceId')]);
        return [     
            'success'=>true,
            'message'=>'Device added',
        ];
    }

    public function guestLogin(Request $request){

        $request->validate([
            'guestId'=>'required|numeric|exists:users,id',
        ]);

        $user = User::find($request->guestId);

        auth()->login($user);

        if($user->guest){
            return [
                'success' => true,
                'message' => [
                    'id' =>  $user->id,
                    'name' =>  $user->name,
                    'email' =>  $user->email,
                    'token'=>  $user->createToken('guest')->plainTextToken]
            ];
        }else{
            return [
                'success'=>false,
                'message'=>'not a guest',
            ];
        }
    }

    public function getPoints(){
        $user = auth()->user();

        return [
            'success' => true,
            'message' => [
                'id' =>  $user->id,
                'name' =>  $user->name,
                'points' => $user->points,
                'free_points' => $user->free_points,
            ]
        ];
    }

    public function setVIP(Request $request){
        $user = auth()->user();

        $pin = Pin::where('pin_code', $request->serialNum)->first();

        if($user->VIP || $pin == null){
            return [
                'success' => false,
            ];
        }

        if(!$pin->is_activate){
            return [
                'success' => false,
            ];
        }

        $user->VIP = true;
        $pin->is_activate = false;
        $pin->save();
        $user->save();

        return [
            'success' => true,
            'message' => "You're a VIP now !"
        ];
    }
}
