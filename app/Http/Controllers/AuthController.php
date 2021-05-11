<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\JWTAuth;
use App\Repositories\UserRepository;

class AuthController extends Controller
{
    /**
     * @var \Tymon\JWTAuth\JWTAuth
     */
    protected $jwt;
    protected $userRepository;
    /**
     * Function constructor
     *
     * @param UserRepository $userRepository
     */
   
    public function __construct(JWTAuth $jwt, UserRepository $userRepository)
    {
        $this->jwt = $jwt;
        $this->userRepository = $userRepository;
        $this->middleware('auth:api',['except'=>['login','register']]);
    }
   
    public function register(Request $request)
    {
        $this->validate($request,[
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required',
        ]);

        try{
            $value = [
                'name'=>$request->input('name'),
                'email' => $request->input('email'),
                'password' => app('hash')->make($request->input('password')),
                'role_id' => $request->input('role_id')
            ];
            if($this->userRepository->create($value)){
                return response()->json(['message'=>'Created successfully',200]);
            }else{
                return response()->json(['message'=>'Created fail',200]);
            }
        }
        catch(\Exception $e){
            return response()->json(['message'=>'Error',400]);
        }
    }
     
    public function login(Request $request)
    {
        $this->validate($request, [
            'email'    => 'required|email|max:255',
            'password' => 'required',
        ]);

        try {

            if (! $token = $this->jwt->attempt($request->only('email', 'password'))) {
                return response()->json(['user_not_found'], 404);
            }

        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {

            return response()->json(['token_absent' => $e->getMessage()], 500);

        }
       
        return response()->json([
            'user'=> $this->jwt->user(),
            'token'=>$token,
            'role'=>$this->jwt->user()->role->name
        ]);
    }

    public function me(Request $request)
    {
        $user = $this->jwt->User();
        return response()->json($user,200);
    }

    public function logout()
    {
        $this->jwt->parseToken()->invalidate();
		return response()->json(['message'=>'Logout successfully'],200);
    }
   
    public function refresh()
    {
        return response()->json([
            'token' => $this->jwt->refresh($this->jwt->getToken())
        ],200);
    }
}
