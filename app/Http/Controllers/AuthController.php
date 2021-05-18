<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Tymon\JWTAuth\JWTAuth;
use App\Repositories\UserRepository;
use App\Mail\MailVerify;
use App\Enum\Verify;
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
            'password' => 'required|confirmed',
        ]);

        try{
            $email = $request->input('email');
            $pos = true;
            while($pos != false)
            {
                $code = app('hash')->make("abcxyz{{$request->input('password')}}");
                $pos = strpos($code,'/');
            }
            $value = [
                'name'=>$request->input('name'),
                'email' => $request->input('email'),
                'password' => app('hash')->make($request->input('password')),
                'role_id' => $request->input('role_id'),
                'verification_code' =>$code
            ];
            if($this->userRepository->create($value)){
                Mail::to($email)->send(new MailVerify($code));
                return response()->json(['message'=>'Register successfully'],200);       
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
            // if($this->jwt->user()->is_verified != Verify::VERIFY)
            // {
            //     JWTAuth::setToken($token)->invalidate();
            //     return response()->json(['message'=>'You must verify your account',200]);
            // }
            return response()->json([
                'user'=> $this->jwt->user(),
                'token'=>$token,
                'role'=>$this->jwt->user()->role->name
            ]);

        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {

            return response()->json(['token_absent' => $e->getMessage()], 500);

        }
       
       
    }

    public function me(Request $request)
    {
        $user = $this->jwt->user();
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

    public function verify($code)
    {
        $user = $this->userRepository->findByField('verification_code',$code);
        if(count($user) >0)
        {
            $value = [
                'is_verified'=>1,
                'verification_code'=>''
            ];
            $id = $user[0]->id;
            if($this->userRepository->update($value,$id))
            {
                return response()->json([
                    'message' => 'Verified email succesfully',
                ],200);
            }        
        }
        return response()->json([
            'message' => 'Verified email failed', 
        ],400);
    }
    
    public function resendEmail($id)
    {
        $user = $this->userRepository->findById($id);
        $email = $user->email;
        $code = $user->verification_code;
        if($user->is_verified == Verify::VERIFY){
            return response()->json(['message'=>"You've verified email"], 200);
        }
       
        Mail::to($email)->send(new MailVerify($code));
        return response()->json(['message'=>'Resend email successfully'], 200);
          
    }

}
