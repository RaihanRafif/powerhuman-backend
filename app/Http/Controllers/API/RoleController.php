<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateRoleRequest;
use App\Http\Requests\updateRoleRequest;
use App\Models\Role;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleController extends Controller
{
    public function create(CreateRoleRequest $request)
    {
        try {
            $role = Role::create([
                'name' => $request->name,
                'company_id' => $request->company_id,
            ]);

            if (!$role) {
                throw  new Exception('Role not created');
            }

            return ResponseFormatter::success($role, 'Role Created');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage());
        }
    }

    public function update(updateRoleRequest $req, $id)
    {
        try {
            $role = Role::find($id);

            if (!$role) {
                throw new Exception('Role not found');
            }

            // Initialize an array to hold the update data
            $updateData = [
                'name' => $req->name,
                'company_id' => $req->company_id,
            ];

            // Update the role with the new data (name and possibly icon)
            $role->update($updateData);

            return ResponseFormatter::success($role, 'Role Updated');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }

    public function fetch(Request $request)
    {
        $id = $request->input('id');
        $limit = $request->input('limit', 10);
        $name = $request->input('name');
        $with_responsibilities = $request->input('with_responsibilities', 0);

        $roleQuery = Role::query();

        if ($id) {
            $role = $roleQuery->with('responsibilities')->find($id);

            if ($role) {
                return ResponseFormatter::success($role, 'Role found');
            }

            return ResponseFormatter::error('Role not found', 404);
        }

        $roles = $roleQuery->where('company_id', $request->company_id);

        if ($name) {
            $roles->where('name', 'like', '%' . $name . '%');
        }

        if ($with_responsibilities) {
            $roles->with('responsibilities');
        }

        return ResponseFormatter::success(
            $roles->paginate($limit),
            'Roles Found'
        );
    }

    public function destroy($id)
    {
        try {
            $role = Role::find($id);

            if (!$role) {
                throw new Exception('Role not found');
            }

            $role->delete();

            return ResponseFormatter::success('Role deleted');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }
}
