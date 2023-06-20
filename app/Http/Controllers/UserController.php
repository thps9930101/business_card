<?php

namespace App\Http\Controllers;

use App\Http\Resources\User;
use Illuminate\Http\Request;


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
        ];
    }


}
