<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class AuthController extends Controller
{

    public function register(Request $request)
    {
        $this->validate($request,[
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required',
        ]);

        $user = new User;
        try{
            $user->name = $request->input('name');
            $user->email = $request->input('email');
            $plainPassword = $request->input('password');
            $user->password = app('hash')->make($plainPassword);
            $user->role_id = $request->input('role_id');
            $user->save();
            return response()->json(['user'=>$user,'message'=>'Created successfully',200]);
        }
        catch(\Exception $e){
            return response()->json(['message'=>'Error',400]);
        }
    }

    public function login(Request $request)
    {
        $this->validate($request,[
            'email' =>'required|string',
            'password'=>'required'
        ]);

        $credentials = $request->only(['email', 'password']);
        if (!$token = Auth::attempt($credentials)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
       
        return response()->json([
            'user'=>Auth::user(),
            'role'=>Auth::user()->role->name,
            'token' => $token,
            'expires_in' => Auth::factory()->getTTL() * 60
        ], 200);
        
    }
}
