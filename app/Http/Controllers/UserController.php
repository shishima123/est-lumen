<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\UserRepository;
use Validator;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    // variable global
    protected $userRepo;
    /**
     * Function constructor
     *
     * @param UserRepository $_userRepository
     */
    public function __construct(
        UserRepository $_userRepository
    ) {
        $this->userRepo = $_userRepository;
    }

    public function index()
    {
        $users = $this->userRepo->getData();
        return response()->json($users);
    }

    public function search(Request $request)
    {
        $searchData =  $request->get('search');
        $users = $this->userRepo->search($searchData);
        return response()->json($users);
    }

    public function show($id)
    {
        $user = $this->userRepo->findById($id);
        return response()->json($user);
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'email' => 'required|email|unique:users',
                'password' => 'required|min:8',
                'role_id' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json(['message' => $validator->errors()], 401);
            }
            $input = $request->only('name', 'email', 'password', 'role_id');
            $result = $this->userRepo->create($input);
        } catch (\Exception $e) {
            Log::error('User Fail Created!', [$e->getMessage()]);
            return response()->json(['errorMessage' => 'User Fail Created!']);
        }

        return response()->json('User Successfully Created!');
    }

    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'password' => 'required|min:8',
                'role_id' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json(['message' => $validator->errors()], 401);
            }
            $data = $this->userRepo->findById($id);
            $input = $request->only('name', 'password', 'role_id');
            $result = $this->userRepo->update($input, $id);
        } catch (\Exception $e) {
            Log::error('User Fail Updated!', [$e->getMessage()]);
            return response()->json(['errorMessage' => 'User Fail Updated!']);
        }

        return response()->json('User Successfully Updated!');
    }

    public function delete($id)
    {
        $data = $this->userRepo->findById($id);
        $this->userRepo->delete($id);

        return response()->json('User Successfully Deleted!');
    }
}
