<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\TeamRepository;
use Validator;
use Illuminate\Support\Facades\Log;

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
            $validator = Validator::make($request->all(), [
                'name' => 'required|unique:teams'
            ]);

            if ($validator->fails()) {
                return response()->json(['message' => $validator->errors()], 401);
            }
            $input = $request->only('name');
            $result = $this->teamRepo->create($input);
        } catch (\Exception $e) {
            Log::error('Team Fail Created!', [$e->getMessage()]);
            return response()->json(['errorMessage' => 'Team Fail Created!'], 400);
        }

        return response()->json('Team Successfully Created!');
    }

    public function update(Request $request, $id)
    {
        try {
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
        $data = $this->teamRepo->findById($id);
        $this->teamRepo->delete($id);

        return response()->json('Team Successfully Deleted!');
    }
}
