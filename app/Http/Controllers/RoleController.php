<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\RoleRepository;
use Validator;
use Illuminate\Support\Facades\Log;

class RoleController extends Controller
{
    // variable global
    protected $roleRepo;
    /**
     * Function constructor
     *
     * @param RoleRepository $_roleRepository
     */
    public function __construct(
        RoleRepository $_roleRepository
    ) {
        $this->roleRepo = $_roleRepository;
    }

    public function index()
    {
        $roles = $this->roleRepo->getData();
        return response()->json($roles);
    }

    public function search(Request $request)
    {
        $searchData =  $request->get('search');
        $roles = $this->roleRepo->search($searchData);
        return response()->json($roles);
    }

    public function show($id)
    {
        $role = $this->roleRepo->findById($id);
        return response()->json($role);
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|unique:roles'
            ]);

            if ($validator->fails()) {
                return response()->json(['message' => $validator->errors()], 401);
            }
            $input = $request->only('name');
            $result = $this->roleRepo->create($input);
        } catch (\Exception $e) {
            Log::error('Role Fail Created!', [$e->getMessage()]);
            return response()->json(['errorMessage' => 'Role Fail Created!'], 400);
        }

        return response()->json('Role Successfully Created!');
    }

    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|unique:roles',
            ]);
            if ($validator->fails()) {
                return response()->json(['message' => $validator->errors()], 401);
            }
            $data = $this->roleRepo->findById($id);
            $input = $request->only('name');
            $result = $this->roleRepo->update($input, $id);
        } catch (\Exception $e) {
            Log::error('Role Fail Updated!', [$e->getMessage()]);
            return response()->json(['errorMessage' => 'Role Fail Updated!'], 400);
        }
        return response()->json('Role Successfully Updated!');
    }

    public function delete($id)
    {
        $data = $this->roleRepo->findById($id);
        $this->roleRepo->delete($id);

        return response()->json('Role Successfully Deleted!');
    }
}
