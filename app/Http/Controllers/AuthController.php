<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Tymon\JWTAuth\JWTAuth;
use App\Repositories\UserRepository;
use App\Mail\MailVerify;
use App\Enum\Verify;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

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
        $this->middleware('auth:api',['except'=>['login','register','verify','resend']]);
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
            $var = Str::random(60);
            $value = [
                'name'=>$request->input('name'),
                'email' => $request->input('email'),
                'password' => app('hash')->make($request->input('password')),
                'role_id' => $request->input('role_id'),
                'verification_code' =>$var
            ];
            if($this->userRepository->create($value)){
                Mail::to($email)->send(new MailVerify($var));
                return response()->json(['message'=>'Register successfully']);       
            }else{
                return response()->json(['message'=>'Created fail',400]);
            }
        }
        catch(\Exception $e){
            return response()->json(['message'=>'Something was wrong',400]);
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
                return response()->json(['User not found'], 404);
            }
            if($this->jwt->user()->is_verified != Verify::VERIFY)
            {
                $this->jwt->setToken($token)->invalidate();
                return response()->json(['message'=>'You must verify your account',400]);
            }
            return response()->json([
                'user'=> $this->jwt->user(),
                'token'=>$token,
                'token_type' => 'bearer',
                'expires_in' => auth()->factory()->getTTL() * 60,
                'role'=>$this->jwt->user()->role->name
            ]);

        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {

            return response()->json(['token absent' => $e->getMessage()], 500);

        }
        catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {

            return response()->json(['token_expired'], 500);

        }
       
       
    }

    public function me(Request $request)
    {
        try{
            $user = $this->jwt->user();
            return response()->json($user);
        }
        catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {

            return response()->json(['token_expired'], 500);

        }
       
    }

    public function logout()
    {
        $this->jwt->parseToken()->invalidate();
		return response()->json(['message'=>'Logout successfully']);
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
                ]);
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
            return response()->json(['message'=>"You've verified email"]);
        } 
        Mail::to($email)->send(new MailVerify($code));
        return response()->json(['message'=>'Resend email successfully']);
          
    }

}
