<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\UserTeamRepository;
use Validator;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\JWTAuth;
use App\Enum\RoleUserTeam;
use App\Repositories\UserRepository;
use App\Enum\Paginate;

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

    public function index(Request $request)
    {
        $perPage =  $request->get('per_page', Paginate::PAGINATE);
        $userTeams = $this->userTeamRepo->getData($perPage);
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
                'email' => 'required|email',
                'team_id'=>'required'
            ]);

            if ($validator->fails()) {
                return response()->json(['message' => $validator->errors()], 401);
            }

            $user = $this->userRepo->findByField('email',$request->input('email'))->first();
            if(!$user)
            {
                return response()->json(['message'=>'User not found'], 400);
            }

            $auth_id = $this->jwt->user()->id;
            $team_id = $request->get('team_id');
            $userInTeam = $this->userTeamRepo->findUserInTeam($user->id, $team_id);

            if($userInTeam)
            {
                return response()->json(['message'=>'User is in team'], 400);
            }

            $msg = $this->userTeamRepo->checkPermission($auth_id, $team_id);

            if ($msg != '') {
                return response()->json(['message' => $msg], 400);
            }

            $input = [
                'user_id'=>$user->id,
                'team_id'=>$team_id,
                'role'=>RoleUserTeam::MEMBER
            ];

            $result = $this->userTeamRepo->create($input);
            return response()->json('UserTeam Successfully Created!');
        } catch (\Exception $e) {
            Log::error('UserTeam Fail Created!', [$e->getMessage()]);
            return response()->json(['errorMessage' => 'UserTeam Fail Created!'], 400);
        }
    }

    public function changeAdmin(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'user_id' => 'required',
                'team_id'=> 'required'
            ]);

            if ($validator->fails()) {
                return response()->json(['message' => $validator->errors()], 401);
            }

            $user_id = $request->get('user_id');
            $team_id = $request->get('team_id');
            $role = $request->get('role');
            $auth_id = $this->jwt->user()->id;

            $role_id = RoleUserTeam::MEMBER;
            $msg = $this->userTeamRepo->checkPermission($auth_id, $team_id);

            if ($msg != '') {
                return response()->json(['message' => $msg], 400);
            }

            $userInTeam = $this->userTeamRepo->findUserInTeam($user_id, $team_id);
            if(!$userInTeam)
            {
                return response()->json(['message'=>'User not in team'], 400);
            }

            if ($role == RoleUserTeam::ADMIN) {
                $role_id = RoleUserTeam::ADMIN;
            }
            $result = $this->userTeamRepo->changeAdmin($userInTeam->id, $role_id);
            return response()->json(['message' => 'Change admin successfully']);
        } catch (\Exception $e) {
             Log::error('Change admin failed', [$e->getMessage()]);
            return response()->json(['errorMessage' => 'Change admin failed'], 400);
        }
    }

    public function removeUserInTeam(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'user_id' => 'required',
                'team_id'=> 'required'
            ]);

            if ($validator->fails()) {
                return response()->json(['message' => $validator->errors()], 401);
            }

            $user_id = $request->get('user_id');
            $team_id = $request->get('team_id');
            $auth_id = $this->jwt->user()->id;

            $msg = $this->userTeamRepo->checkPermission($auth_id, $team_id);
            if ($msg != '') {
                return response()->json(['message' => $msg], 400);
            }

            if($auth_id == $user_id)
            {
                return response()->json(['message'=>'You could not do this'], 400);
            }

            $userInTeam = $this->userTeamRepo->findUserInTeam($user_id, $team_id);
            if(!$userInTeam)
            {
                return response()->json(['message'=>'User not in team'], 400);
            }

            if($userInTeam->role == RoleUserTeam::OWNER)
            {
                return response()->json(['message'=>'You can not delete owner team'], 400);
            }

            $this->userTeamRepo->delete($userInTeam->id);
            return response()->json(['message' => 'Delete user in team successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Delete user in team fail'], 400);
        }
    }
}
