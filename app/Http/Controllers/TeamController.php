<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\TeamRepository;
use App\Repositories\UserTeamRepository;
use Validator;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\JWTAuth;
use App\Enum\RoleUserTeam;

class TeamController extends Controller
{
    // variable global
    protected $teamRepo;
    protected $userTeamRepo;
    protected $jwt;
    /**
     * Function constructor
     *
     * @param TeamRepository $_teamRepository
     * @param UserTeamRepository $_userTeamRepository
     */
    public function __construct(
        TeamRepository $_teamRepository,
        UserTeamRepository $_userTeamRepository,
        JWTAuth $jwt
    ) {
        $this->teamRepo = $_teamRepository;
        $this->userTeamRepo = $_userTeamRepository;
        $this->jwt = $jwt;
        $this->middleware('auth:api');
    }

    public function index()
    {
        $teams = $this->teamRepo->getData();
        return response()->json($teams);
    }

    public function search(Request $request)
    {
        $searchData =  $request->get('search');
        $teams = $this->teamRepo->search($searchData);
        return response()->json($teams);
    }

    public function show($id)
    {
        $team = $this->teamRepo->findById($id);
        return response()->json($team);
    }

    public function store(Request $request)
    {
        try {
            $user_id = $this->jwt->user()->id;
            $validator = Validator::make($request->all(), [
                'name' => 'required|unique:teams'
            ]);

            if ($validator->fails()) {
                return response()->json(['message' => $validator->errors()], 401);
            }
            $input = $request->only('name');
            $result = $this->teamRepo->create($input);
            $team_id = $this->teamRepo->findByField('name',$request->input('name'))[0]->id;
            $value = [
                'user_id' => $user_id,
                'team_id' => $team_id,
                'role' => RoleUserTeam::OWNER
            ];
            $owner_team = $this->userTeamRepo->create($value);

        } catch (\Exception $e) {
            Log::error('Team Fail Created!', [$e->getMessage()]);
            return response()->json(['errorMessage' => 'Team Fail Created!'], 400);
        }

        return response()->json('Team Successfully Created!');
    }

    public function update(Request $request, $id)
    {
        try {
            $msg = $this->checkPermission($id);
            if($msg != ''){
                return response()->json(['message'=>$msg],400);
            }
            $validator = Validator::make($request->all(), [
                'name' => 'required|unique:teams',
            ]);
            if ($validator->fails()) {
                return response()->json(['message' => $validator->errors()], 401);
            }
            $data = $this->teamRepo->findById($id);
            $input = $request->only('name');
            $result = $this->teamRepo->update($input, $id);
        } catch (\Exception $e) {
            Log::error('Team Fail Updated!', [$e->getMessage()]);
            return response()->json(['errorMessage' => 'Team Fail Updated!'], 400);
        }
        return response()->json('Team Successfully Updated!');
    }

    public function delete($id)
    {
        $user_id = $this->jwt->user()->id;
        $userInTeam =  $this->userTeamRepo->findUserInTeam($user_id,$id);
        if($userInTeam != null){
            if($userInTeam->role !=RoleUserTeam::OWNER){
                return response()->json(['message'=>"You don't have permission for that"],400);
            }
        }
        $data = $this->teamRepo->findById($id);
        $this->teamRepo->delete($id);
        //del users in team
        $usersInTeam = $this->userTeamRepo->findByField('team_id',$id);
        for($i=0;$i<count($usersInTeam)-1;$i++)
        {
            $this->userTeamRepo->delete($usersInTeam[$i]->id);
        }
        return response()->json('Team Successfully Deleted!');
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
