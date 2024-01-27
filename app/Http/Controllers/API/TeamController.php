<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateTeamRequest;
use App\Http\Requests\updateTeamRequest;
use App\Models\Team;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeamController extends Controller
{
    public function create(CreateTeamRequest $request)
    {
        try {
            $path = null;
            if ($request->hasFile('icon')) {
                $path = $request->file('icon')->store('public/icons');
            }

            $team = Team::create([
                'name' => $request->name,
                'icon' => $path,
                'company_id' => $request->company_id,
            ]);

            if (!$team) {
                throw  new Exception('Team not created');
            }

            return ResponseFormatter::success($team, 'Team Created');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage());
        }
    }

    public function update(updateTeamRequest $req, $id)
    {
        try {
            $team = Team::find($id);

            if (!$team) {
                throw new Exception('Team not found');
            }

            // Check if a new icon file was uploaded
            if ($req->hasFile('icon')) {
                $path = $req->file('icon')->store('public/icons');
            }

            $team->update([
                'name' => $req->name,
                'company_id' => $req->company_id,
                'icon' => isset($path) ? $path : $team->icon
            ]);

            return ResponseFormatter::success($team, 'Team Updated');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }

    public function fetch(Request $request)
    {
        $id = $request->input('id');
        $limit = $request->input('limit', 10);
        $name = $request->input('name');

        $teamQuery = Team::query();

        if ($id) {
            $team = $teamQuery->find($id);

            if ($team) {
                return ResponseFormatter::success($team, 'Team found');
            }

            return ResponseFormatter::error('Team not found', 404);
        }

        $teams = $teamQuery->where('company_id', $request->company_id);

        if ($name) {
            $teams->where('name', 'like', '%' . $name . '%');
        }

        return ResponseFormatter::success(
            $teams->paginate($limit),
            'Teams Found'
        );
    }

    public function destroy($id)
    {
        try {
            $team = Team::find($id);

            if (!$team) {
                throw new Exception('Team not found');
            }

            $team->delete();

            return ResponseFormatter::success('Team deleted');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }
}
