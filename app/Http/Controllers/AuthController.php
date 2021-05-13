<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Tymon\JWTAuth\JWTAuth;
use App\Repositories\UserRepository;
<<<<<<< HEAD
=======
use Illuminate\Support\Facades\Mail;
use App\Mail\MailVerify;
use App\Enum\Verify;
>>>>>>> 393605f (Fix PR verify_email)

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
<<<<<<< HEAD
<<<<<<< HEAD
        $this->middleware('auth:api',['except'=>['login','register']]);
=======
        $this->middleware('auth:api',['except'=>['login','register','verify','resendEmail']]);
>>>>>>> 39511ae (check verified email and resend email)
=======
        $this->middleware('auth:api',['except'=>['login','register','verify','resendEmail','alo']]);
>>>>>>> 393605f (Fix PR verify_email)
    }
   
    public function register(Request $request)
    {
        $this->validate($request,[
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed',
        ]);

        try{
<<<<<<< HEAD
=======
            $email = $request->input('email');
<<<<<<< HEAD
            $code = app('hash')->make($request->input('password'));
>>>>>>> 39511ae (check verified email and resend email)
=======
            $pos = true;
            while($pos != false)
            {
                $code = app('hash')->make("abcxyz{{$request->input('password')}}");
                $pos = strpos($code,'/');
            }
>>>>>>> 393605f (Fix PR verify_email)
            $value = [
                'name'=>$request->input('name'),
                'email' =>$email,
                'password' => app('hash')->make($request->input('password')),
                'role_id' => $request->input('role_id')
            ];
            if($this->userRepository->create($value)){
<<<<<<< HEAD
<<<<<<< HEAD
=======
                Mail::to($email)->send(new MyEmail($code));
>>>>>>> 39511ae (check verified email and resend email)
                return response()->json(['message'=>'Created successfully',200]);
=======
                $mail = new MailVerify($code);
                Mail::to($email)->send($mail);
                return response()->json(['message'=>'Register successfully'],200);       
>>>>>>> 393605f (Fix PR verify_email)
            }else{
                return response()->json(['message'=>'Register failed',200]);
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
                return response()->json(['user_not_found'], 404);
            }
            if($this->jwt->user()->is_verified ==Verify::VERIFY){
                $this->jwt->setToken($token)->invalidate();
                return response()->json([
                    'user'=> $this->jwt->user(),
                    'token'=>$token,
                    'role'=>$this->jwt->user()->role->name
                ]);
            }
            else{
                return response()->json(['message'=>'You have to verify email'], 400);
            }

            
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
<<<<<<< HEAD
=======
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
            'message' => 'Verified email failed'
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
<<<<<<< HEAD
>>>>>>> 39511ae (check verified email and resend email)
=======
    
>>>>>>> 393605f (Fix PR verify_email)
}
