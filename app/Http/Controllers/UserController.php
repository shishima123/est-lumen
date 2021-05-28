<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\UserRepository;
use Validator;
use Illuminate\Support\Facades\Log;
use App\Enum\Paginate;
use Tymon\JWTAuth\JWTAuth;
use App\Enum\RoleUser;

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
            $validate = [
                'name' => 'required',
            ];
            $role_id = '';
            if($auth->role->name == 'admin')
            {
                $validate['role_id'] = 'required';
                $role_id = 'role_id';
            }

            if($auth->role->name == RoleUser::ADMIN || $auth->id == $id)
            {
                $validator = Validator::make($request->all(), $validate);

                if ($validator->fails()) {
                    return response()->json(['message' => $validator->errors()], 401);
                }

                $result = $this->userRepo->update($request->only('name',$role_id), $id);

                return response()->json('User Successfully Updated!');
            }

            return response()->json(['message'=>'You dont have permission for action',$validate], 400);

        } catch (\Exception $e) {
            Log::error('User Fail Updated!', [$e->getMessage()]);
            return response()->json(['errorMessage' => 'User Fail Updated!'], 400);
        }
    }

    public function delete($id)
    {
        try{
            if($this->jwt->user()->role->name != RoleUser::ADMIN){
                return response()->json(['message'=>'You dont have permission for action'], 400);
            }
            if($this->jwt->user()->id == $id)
            {
                return response()->json(['message'=>'You can not delete this user'],400);
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
