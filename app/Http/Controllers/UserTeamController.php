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
                'user_id' => 'required',
                'team_id' => 'required',
                'role' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json(['message' => $validator->errors()], 401);
            }

            $auth_id = $this->jwt->user()->id;
            $team_id = $request->input('team_id');
            $msg = $this->userTeamRepo->checkPermission($auth_id, $team_id);
            if ($msg != '') {
                return response()->json(['message' => $msg], 400);
            }
            $input = $request->only('user_id', 'team_id', 'role');
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
            $auth_id = $this->jwt->user()->id;

            $role_id = RoleUserTeam::MEMBER;
            $msg = $this->userTeamRepo->checkPermission($auth_id, $team_id);

            if ($msg != '') {
                return response()->json(['message' => $msg], 400);
            }

            $user_id = $this->userRepo->findByField('email', $email)[0]->id;
            $userInTeamId = $this->userTeamRepo->findUserInTeam($user_id, $team_id)->id;

            if ($role == 'admin') {
                $role_id = RoleUserTeam::ADMIN;
            }
            $result = $this->userTeamRepo->changeAdmin($userInTeamId, $role_id);
            return response()->json(['message' => 'Change admin successfully']);
        } catch (\Exception $e) {
            return response()->json(['errorMessage' => 'Change admin fail'], 400);
        }
    }

    public function removeUserInTeam(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required',
                'team_id' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json(['message' => $validator->errors()], 401);
            }
            $email = $request->input('email');
            $team_id = $request->input('team_id');
            $auth_id = $this->jwt->user()->id;

            $msg = $this->userTeamRepo->checkPermission($auth_id, $team_id);
            if ($msg != '') {
                return response()->json(['message' => $msg], 400);
            }

            $user_id = $this->userRepo->findByField('email', $email)[0]->id;
            $userInTeamId = $this->userTeamRepo->findUserInTeam($user_id, $team_id)->id;
            $this->userTeamRepo->delete($userInTeamId);
            return response()->json(['message' => 'Delete user in team successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Delete user in team fail'], 400);
        }
    }
}
