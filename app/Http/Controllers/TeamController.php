<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\TeamRepository;

class TeamController extends Controller
{
    // variable global
    protected $teamRepo;
    /**
     * Function constructor
     *
     * @param TeamRepository $_teamRepository
     */
    public function __construct(
        TeamRepository $_teamRepository
    ) {
        $this->teamRepo = $_teamRepository;
    }

    public function index()
    {
        $teams = $this->teamRepo->getData();
        return response()->json($teams);
    }

    public function show($id)
    {
        $team = $this->teamRepo->findById($id);
        return response()->json($team);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|unique:teams'
        ]);
        $input = $request->only('name');
        $result = $this->teamRepo->create($input);

        return response()->json('Team Successfully Created!');
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required|unique:teams',
        ]);
        $data = $this->teamRepo->findById($id);
        $input = $request->only('name');
        $result = $this->teamRepo->update($input, $id);
        return response()->json('Team Successfully Updated!');
    }

    public function delete($id)
    {
        $data = $this->teamRepo->findById($id);
        $this->teamRepo->delete($id);

        return response()->json('Team Successfully Deleted!');
    }
}
