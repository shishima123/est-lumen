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
        DB::beginTransaction();
        try {
            $user_id = $this->jwt->user()->id;
            $validator = Validator::make($request->all(), [
                'name' => 'required|unique:teams'
            ]);

            if ($validator->fails()) {
                return response()->json(['message' => $validator->errors()], 401);
            }
            $input = $request->only('name');
            $team = $this->teamRepo->create($input);
            $team_id = $team->id;
            $value = [
                'user_id' => $user_id,
                'team_id' => $team_id,
                'role' => RoleUserTeam::OWNER
            ];
            $owner_team = $this->userTeamRepo->create($value);
            DB::commit();
            return response()->json('Team Successfully Created!');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Team Fail Created!', [$e->getMessage()]);
            return response()->json(['errorMessage' => 'Team Fail Created!'], 400);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $user_id = $this->jwt->user()->id;
            $msg = $this->userTeamRepo->checkPermission($user_id,$id);
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
        DB::beginTransaction();
        try{
            $user_id = $this->jwt->user()->id;
            $userInTeam =  $this->userTeamRepo->findUserInTeam($user_id,$id);
            if($userInTeam != null){
                if( !$userInTeam->isOwnerRole() ){
                    return response()->json(['message'=>"You don't have permission for that"],400);
                }
            }
            $data = $this->teamRepo->findById($id);
            $this->teamRepo->delete($id);
            //del users in team
            $usersInTeam = $this->userTeamRepo->findByField('team_id',$id);
            $this->userTeamRepo->deleteMultiple($usersInTeam);
            DB::commit();
            return response()->json('Team Successfully Deleted!');
        }
        catch (\Exception $e) {
            DB::rollback();
            return response()->json(['errorMessage' => 'Delete users in team fail'], 400);
        }
    }

}
