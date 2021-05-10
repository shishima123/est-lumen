<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Repositories\UserRepository;

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

    public function show($id)
    {
        $user = $this->userRepo->findById($id);
        return response()->json($user);
    }

    public function create(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
            'role_id' => 'required',
        ]);
        $input = $request->only('name', 'email', 'password', 'role_id');
        $result = $this->userRepo->create($input);

        return response()->json('User Successfully Created!');
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required',
            'password' => 'required|min:8',
            'role_id' => 'required',
        ]);
        $data = $this->userRepo->findById($id);
        $input = $request->only('name', 'password', 'role_id');
        $result = $this->userRepo->update($input, $id);
        return response()->json('User Successfully Updated!');
    }

    public function delete($id)
    {
        $data = $this->userRepo->findById($id);
        $this->userRepo->delete($id);

        return response()->json('User Successfully Deleted!');
    }
}
