<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Tymon\JWTAuth\JWTAuth;
use App\Repositories\UserRepository;
use App\Repositories\RoleRepository;
use App\Mail\MailVerify;
use App\Enum\Verify;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\MailForgotPass;


class AuthController extends Controller
{
    /**
     * @var \Tymon\JWTAuth\JWTAuth
     */
    protected $jwt;
    protected $userRepository;
    protected $roleRepository;
    /**
     * Function constructor
     *
     * @param UserRepository $userRepository
     * @param RoleRepository $_roleRepository
     */

    public function __construct(JWTAuth $jwt, UserRepository $userRepository, RoleRepository $_roleRepository)
    {
        $this->jwt = $jwt;
        $this->userRepository = $userRepository;
        $this->roleRepository = $_roleRepository;
        $this->middleware('auth:api',['except'=>['login','register','verify','resendEmail','sendMailForgotPass','checkIdentificationCode','newPassword']]);
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
                'role_id' => $this->roleRepository->findByField('name','member')->first()->id,
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

    public function sendMailForgotPass(Request $request)
    {
        try{
            $email = $request->input('email');
            $findEmail = $this->userRepository->findByField('email',$email)->first();
            if(!$findEmail)
            {
                return response()->json(['message'=>'email does not exists'], 400);
            }
            $code = Str::random(6);
            try{
                Mail::to($email)->send(new MailForgotPass($code));
                $value = ['identification_code'=>$code];
                $this->userRepository->update($value,$findEmail->id);
                return response()->json(['message'=>'Send mail successfully']);
            }catch(Exception $e){
                return response()->json(['message'=>"Send mail failed"],400);
            }
        }catch(Exception $e)
        {
            return response()->json(['message'=>'Something was wrong'], 400);
        }
    }
    public function checkIdentificationCode (Request $request)
    {
        $this->validate($request,[
            'code' => 'required|size:6'
        ]);
        $code = $request->input('code');
        $findCode = $this->userRepository->findByField('identification_code',$code)->first();
        if(!$findCode)
        {
            return response()->json(['message'=>'Code is not identification'], 400);
        }
        $value = ['identification_code'=>''];
        $this->userRepository->update($value,$findCode->id);
        return response()->json(['message'=>'Verifiy identification code successfully','idUser'=>$findCode->id,'email'=>$findCode->email]);
    }
    public function newPassword(Request $request,$idUser,$email)
    {
        $this->validate($request,[
            'password' => 'required|confirmed|min:8',
        ]);
        try{
            $value =['password' => app('hash')->make($request->input('password'))];
            $findEmail = $this->userRepository->findByField('email',$email)->first();
            if(!$findEmail)
            {
                return response()->json(['message'=>'Email does not exists'],400);
            }
            if($idUser != $findEmail->id)
            {
                return response()->json(['message'=>'Unauthorized'], 400);
            }
            if($findEmail->identification_code !='')
            {
                return response()->json(['message'=>'You must have enter your identification code is sent to your email'], 400);
            }
            if(!$this->userRepository->update($value,$idUser))
            {
                return response()->json(['message'=>'Change password failed'],400);
            }
            return response()->json(['message'=>'Change password successfully']);
        }catch(Exception $e)
        {
            return response()->json(['message'=>'Change password failed'],400);
        }
    }
}
