<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\UserTeamRepository;
use Validator;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\JWTAuth;
use App\Enum\RoleUserTeam;
use App\Repositories\UserRepository;

class UserTeamController extends Controller
{
    // variable global
    protected $userTeamRepo;
    protected $userRepo;
    protected $jwt;
    /**
     * Function constructor
     *
     * @param UserTeamRepository $_userTeamRepository
     */
    public function __construct(
        UserTeamRepository $_userTeamRepository,
        UserRepository $_userRepository,
        JWTAuth $jwt
    ) {
        $this->userTeamRepo = $_userTeamRepository;
        $this->userRepo = $_userRepository;
        $this->jwt = $jwt;
        $this->middleware('auth:api');
    }

    public function index()
    {
        $userTeams = $this->userTeamRepo->getData();
        return response()->json($userTeams);
    }

    public function show($id)
    {
        $userTeam = $this->userTeamRepo->findById($id);
        return response()->json($userTeam);
    }

    public function store(Request $request)
    {

        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required',
                'team_id' => 'required',
                'role' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json(['message' => $validator->errors()], 401);
            }
            $msg = $this->checkPermission($request->input('team_id'));
            if($msg != ''){
                return response()->json(['message'=>$msg],400);
            }
            $input = $request->only('user_id', 'team_id', 'role');
            $result = $this->userTeamRepo->create($input);
        } catch (\Exception $e) {
            Log::error('UserTeam Fail Created!', [$e->getMessage()]);
            return response()->json(['errorMessage' => 'UserTeam Fail Created!'], 400);
        }

        return response()->json('UserTeam Successfully Created!');
    }

    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required',
                'team_id' => 'required',
                'role' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json(['message' => $validator->errors()], 401);
            }
            $data = $this->userTeamRepo->findById($id);
            $input = $request->only('user_id', 'team_id', 'role');
            $result = $this->userTeamRepo->update($input, $id);
        } catch (\Exception $e) {
            return response()->json(['errorMessage' => 'UserTeam Fail Updated!'], 400);
        }

        return response()->json('UserTeam Successfully Updated!');
    }

    public function changeAdmin (Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'email' => 'required',
                'team_id' => 'required',
                'role' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json(['message' => $validator->errors()], 401);
            }

            $email = $request->input('email');
            $team_id = $request->input('team_id');
            $role = $request->input('role');

            $role_id = 0;
            $msg = $this->checkPermission($team_id);
            if($msg != ''){
                return response()->json(['message'=>$msg],400);
            }

            $user_id = $this->userRepo->findByField('email',$email)[0]->id;
            $userInTeamId = $this->userTeamRepo->findUserInTeam($user_id,$team_id)->id;

            if($role=='admin')
                $role_id = 2;
            if($role=='member')
                $role_id = 3;
            $result = $this->userTeamRepo->changeAdmin($userInTeamId,$role_id);
        }
        catch (\Exception $e) {
            return response()->json(['errorMessage' => 'Change admin fail'], 400);
        }
        return response()->json(['message'=>'Change admin successfully']);

    }

    public function delete($id)
    {
        $data = $this->userTeamRepo->findById($id);
        $this->userTeamRepo->delete($id);

        return response()->json('UserTeam Successfully Deleted!');
    }

    public function removeUserInTeam(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'email' => 'required',
                'team_id' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json(['message' => $validator->errors()], 401);
            }
            $email = $request->input('email');
            $team_id = $request->input('team_id');

            $msg = $this->checkPermission($team_id);
            if($msg != ''){
                return response()->json(['message'=>$msg],400);
            }

            $user_id = $this->userRepo->findByField('email',$email)[0]->id;
            $userInTeamId = $this->userTeamRepo->findUserInTeam($user_id,$team_id)->id;
            $this->delete($userInTeamId);
        }
        catch (\Exception $e) {
            return response()->json(['message' => 'Delete user in team fail'], 400);
        }
        return response()->json(['message' => 'Delete user in team successfully']);
    }

    public function checkPermission ($team_id)
    {
        $user_id = $this->jwt->user()->id;
        $userInTeam =  $this->userTeamRepo->findUserInTeam($user_id,$team_id);
        $msg = '';
        if($userInTeam != null){
            if($userInTeam->role ==RoleUserTeam::MEMBER){
                $msg = "You don't have permission for that" ;
            }
        }
        return $msg;
    }
}
