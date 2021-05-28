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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

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
     * @param RoleRepository $roleRepository
     */

    public function __construct(JWTAuth $jwt, UserRepository $userRepository, RoleRepository $roleRepository)
    {
        $this->jwt = $jwt;
        $this->userRepository = $userRepository;
        $this->roleRepository = $roleRepository;
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

            $user = $this->userRepository->create($value);
            Mail::to($email)->send(new MailVerify($var));
            return response()->json(['message'=>'Register successfully']);
        }
        catch(\Exception $e){
            Log::error('Register Failed: ' . $e->getMessage());
            return response()->json(['message'=>'Something was wrong'],400);
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
                return response()->json(['message'=>'User not found'], 404);
            }
            if($this->jwt->user()->is_verified != Verify::VERIFY)
            {
                $this->jwt->setToken($token)->invalidate();
                return response()->json(['message'=>'You must verify your account'],400);
            }
            return response()->json([
                'user'=> $this->jwt->user(),
                'token'=>$token,
                'token_type' => 'bearer',
                'expires_in' => auth()->factory()->getTTL() * 60,
                'role'=>$this->jwt->user()->role->name
            ]);

        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {

            return response()->json(['message'=>'token absent'.$e->getMessage()], 500);

        }
        catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {

            return response()->json(['message'=>'token_expired'], 500);

        }
    }

    public function me(Request $request)
    {
        try{
            $user = $this->jwt->user();
            return response()->json($user);
        }
        catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {

            return response()->json(['message'=>'token_expired'], 500);

        }
    }

    public function logout()
    {
        auth()->logout();
        return response()->json(['message'=>'Logout successfully']);
    }

    public function refresh()
    {
        return response()->json([
            'token' => auth()->refresh(),
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }

    public function verify($code)
    {
        try{
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
            else{
                return response()->json(['messsage'=>'Code is unidentification']);
            }
        }
        catch(\Exception $e)
        {
            Log::error('Verify email failed: ' . $e->getMessage());
            return response()->json([
                'message' => 'Verified email failed',
                ],400);
        }
    }

    public function resendEmail($id)
    {
        try{
            $user = $this->userRepository->findById($id);
            if($user == null){
                return response()->json(['message'=>'User not found'], 400);
            }
            $email = $user->email;
            $code = $user->verification_code;

            if($user->is_verified == Verify::VERIFY){
                return response()->json(['message'=>"You've verified email"]);
            }

            Mail::to($email)->send(new MailVerify($code));
            return response()->json(['message'=>'Resend email successfully']);
        }
        catch(\Exception $e)
        {
            Log::error('Resend email failed: ' . $e->getMessage());
            return response()->json(['message'=>'Resend email failed'],400);
        }
    }

    public function sendMailForgotPass(Request $request)
    {
        try{
            $email = $request->input('email');
            $user = $this->userRepository->findByField('email',$email)->first();

            if(!$user)
            {
                return response()->json(['message'=>'Email does not exists'], 400);
            }

            $code = Str::random(6);
            Mail::to($email)->send(new MailForgotPass($code));
            $value = ['identification_code'=>$code];
            $result = $this->userRepository->update($value,$user->id);
            return response()->json(['message'=>'Send mail successfully']);
        }
        catch(\Exception $e)
        {
            Log::error('Send Mail Forgot Password Failed: ' . $e->getMessage());
            return response()->json(['message'=>'Send Mail Forgot Password Failed'], 400);
        }
    }

    public function checkIdentificationCode (Request $request)
    {
        try{
            $this->validate($request,[
                'code' => 'required|size:6'
            ]);

            $code = $request->input('code');
            $user = $this->userRepository->findByField('identification_code',$code)->first();

            if(!$user)
            {
                return response()->json(['message'=>'Code is not identification'], 400);
            }

            $value = ['identification_code'=>''];
            $result = $this->userRepository->update($value,$user->id);
            return response()->json(['message'=>'Verifiy identification code successfully','idUser'=>$user->id,'email'=>$user->email]);
        }
        catch(\Exception $e)
        {
            Log::error('Check identification code failed: ' . $e->getMessage());
            return response()->json(['message'=>'Check identification code failed'], 400);
        }
    }

    public function newPassword(Request $request,$idUser,$email)
    {
        $this->validate($request,[
            'password' => 'required|confirmed|min:8',
        ]);

        try{
            $value =['password' => app('hash')->make($request->input('password'))];
            $user = $this->userRepository->findByField('email',$email)->first();

            if(!$user)
            {
                return response()->json(['message'=>'Email does not exists'],400);
            }

            if($idUser != $user->id)
            {
                return response()->json(['message'=>'Unauthorized'], 400);
            }

            if($user->identification_code !='')
            {
                return response()->json(['message'=>'You must have enter your identification code is sent to your email'], 400);
            }

            if(!$this->userRepository->update($value,$idUser))
            {
                return response()->json(['message'=>'Change password failed'],400);
            }

            return response()->json(['message'=>'Change password successfully']);
        }
        catch(\Exception $e)
        {
            Log::error('Change password Failed: ' . $e->getMessage());
            return response()->json(['message'=>'Change password Failed'],400);
        }
    }

    public function changePassword(Request $request)
    {
        $this->validate($request,[
                'password' => 'required|confirmed|min:8',
                'oldPassword'=>'required|min:8'
            ]);

        try{

            if(!app('hash')->check($request->input('oldPassword'), $this->jwt->user()->password))
            {
                return response()->json(['message'=>'Old password is incorrect'], 400);
            }

            $value =[
                'password' => app('hash')->make($request->input('password'))
            ];

            $result = $this->userRepository->update($value,$this->jwt->user()->id);
            return response()->json(['message'=>'Change password successfully']);
        }
        catch(\Exception $e)
        {
            Log::error('Change password failed'.$e->getMessage());
            return response()->json(['message'=>'Change password failed'],400);
        }
    }
}
