<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\UserTeamRepository;
use Illuminate\Support\Facades\DB;
use Validator;

class UserTeamController extends Controller
{
    // variable global
    protected $userTeamRepo;
    /**
     * Function constructor
     *
     * @param UserTeamRepository $_userteamRepository
     */
    public function __construct(
        UserTeamRepository $_userTeamRepository
    ) {
        $this->userTeamRepo = $_userTeamRepository;
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
            $input = $request->only('user_id', 'team_id', 'role');
            $result = $this->userTeamRepo->create($input);
        } catch (\Exception $e) {
            return response()->json(['errorMessage' => 'UserTeam Fail Created!']);
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
            return response()->json(['errorMessage' => 'UserTeam Fail Updated!']);
        }

        return response()->json('UserTeam Successfully Updated!');
    }

    public function delete($id)
    {
        $data = $this->userTeamRepo->findById($id);
        $this->userTeamRepo->delete($id);

        return response()->json('UserTeam Successfully Deleted!');
    }
}
