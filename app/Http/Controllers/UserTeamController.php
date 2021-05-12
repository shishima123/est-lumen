<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\UserTeamRepository;

class UserTeamController extends Controller
{
    // variable global
    protected $userteamRepo;
    /**
     * Function constructor
     *
     * @param UserTeamRepository $_userteamRepository
     */
    public function __construct(
        UserTeamRepository $_userteamRepository
    ) {
        $this->userteamRepo = $_userteamRepository;
    }

    public function index()
    {
        $userteams = $this->userteamRepo->getData();
        return response()->json($userteams);
    }

    public function show($id)
    {
        $userteam = $this->userteamRepo->findById($id);
        return response()->json($userteam);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'user_id' => 'required',
            'team_id' => 'required',
            'role' => 'required',
        ]);
        $input = $request->only('user_id', 'team_id', 'role');
        $result = $this->userteamRepo->create($input);

        return response()->json('UserTeam Successfully Created!');
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'user_id' => 'required',
            'team_id' => 'required',
            'role' => 'required',
        ]);
        $data = $this->userteamRepo->findById($id);
        $input = $request->only('user_id', 'team_id', 'role');
        $result = $this->userteamRepo->update($input, $id);
        return response()->json('UserTeam Successfully Updated!');
    }

    public function delete($id)
    {
        $data = $this->userteamRepo->findById($id);
        $this->userteamRepo->delete($id);

        return response()->json('UserTeam Successfully Deleted!');
    }
}
