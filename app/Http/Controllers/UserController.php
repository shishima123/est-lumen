<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\UserRepository;
use Validator;
use Illuminate\Support\Facades\Log;
use App\Enum\Paginate;
use Tymon\JWTAuth\JWTAuth;

class UserController extends Controller
{
    // variable global
    /**
     * @var \Tymon\JWTAuth\JWTAuth
     */
    protected $jwt;
    protected $userRepo;
    /**
     * Function constructor
     *
     * @param UserRepository $_userRepository
     */
    public function __construct(
        UserRepository $_userRepository,
        JWTAuth $_jwt
    ) {
        $this->userRepo = $_userRepository;
        $this->jwt = $_jwt;
        $this->middleware('auth:api');
    }

    public function index(Request $request)
    {
        $searchData =  $request->get('search');
        $perPage =  $request->get('per_page', Paginate::PAGINATE);
        $users = $this->userRepo->getData($searchData, $perPage);
        return response()->json($users);
    }

    public function show($id)
    {
        $user = $this->userRepo->findById($id);
        return response()->json($user);
    }

    public function update(Request $request, $id)
    {
        try {
            $auth = $this->jwt->user();
            if($auth->role->name == 'admin')
            {
                $validator = Validator::make($request->all(), [
                'name' => 'required',
                'password' => 'required|min:8',
                'role_id' => 'required',
                ]);

                if ($validator->fails()) {
                    return response()->json(['message' => $validator->errors()], 401);
                }

                $input = $request->only('name', 'password', 'role_id');
                $result = $this->userRepo->update($input, $id);

                return response()->json('User Successfully Updated!');
            }
            if($auth->id == $id)
            {
                $validator = Validator::make($request->all(), [
                'name' => 'required',
                'password' => 'required|min:8',
                ]);

                if ($validator->fails()) {
                    return response()->json(['message' => $validator->errors()], 401);
                }

                $input = $request->only('name', 'password');
                $result = $this->userRepo->update($input, $id);

                return response()->json('User Successfully Updated!');
            }
            return response()->json(['message'=>'You dont have permission for action'], 400);

        } catch (\Exception $e) {
            Log::error('User Fail Updated!', [$e->getMessage()]);
            return response()->json(['errorMessage' => 'User Fail Updated!'], 400);
        }
    }

    public function delete($id)
    {
        try{
            if($this->jwt->user()->roles->name != 'admin'){
                return response()->json(['message'=>'You dont have permission for action'], 400);
            }
            $data = $this->userRepo->findById($id);
            $this->userRepo->delete($id);

            return response()->json('User Successfully Deleted!');
        }
        catch(\Exception $e)
        {
            Log::error('Delete user failed '.$e->getMessage());
            return response()->json(['message'=>'Delete user failed'], 400);
        }
    }
}
