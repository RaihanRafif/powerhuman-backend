<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateEmployeeRequest;
use App\Http\Requests\updateEmployeeRequest;
use App\Models\Employee;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployeeController extends Controller
{
    public function create(CreateEmployeeRequest $request)
    {
        try {
            $path = null;
            if ($request->hasFile('photo')) {
                $path = $request->file('photo')->store('public/icons');
            }

            $employee = Employee::create([
                'name' => $request->name,
                'email' => $request->email,
                'gender' => $request->gender,
                'age' => $request->age,
                'phone' => $request->phone,
                'photo' => $path,
                'team_id' => $request->team_id,
                'role_id' => $request->role_id,
            ]);

            if (!$employee) {
                throw  new Exception('Employee not created');
            }

            return ResponseFormatter::success($employee, 'Employee Created');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage());
        }
    }

    public function update(updateEmployeeRequest $req, $id)
    {
        try {
            $employee = Employee::find($id);

            if (!$employee) {
                throw new Exception('Employee not found');
            }

            // Check if a new icon file was uploaded
            if ($req->hasFile('photo')) {
                $path = $req->file('photo')->store('public/icons');
            }

            $employee->update([
                'name' => $req->name,
                'photo' => isset($path) ? $path : $employee->photo,
                'email' => $req->email,
                'gender' => $req->gender,
                'age' => $req->age,
                'phone' => $req->phone,
                'team_id' => $req->team_id,
                'role_id' => $req->role_id,
            ]);

            return ResponseFormatter::success($employee, 'Employee Updated');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }

    public function fetch(Request $request)
    {
        $id = $request->input('id');
        $limit = $request->input('limit', 10);
        $name = $request->input('name');
        $email = $request->input('email');
        $age = $request->input('age');
        $phone = $request->input('phone');
        $team_id = $request->input('team_id');
        $role_id = $request->input('role_id');

        $employeeQuery = Employee::query();

        if ($id) {
            $employee = $employeeQuery->with(['team', 'role'])->find($id);

            if ($employee) {
                return ResponseFormatter::success($employee, 'Employee found');
            }

            return ResponseFormatter::error('Employee not found', 404);
        }

        $employees = $employeeQuery;

        if ($name) {
            $employees->where('name', 'like', '%' . $name . '%');
        }

        if ($email) {
            $employees->where('email', $email);
        }

        if ($age) {
            $employees->where('age', $age);
        }

        if ($role_id) {
            $employees->where('role_id', $role_id);
        }

        if ($team_id) {
            $employees->where('team_id', $team_id);
        }


        return ResponseFormatter::success(
            $employees->paginate($limit),
            'Employees Found'
        );
    }

    public function destroy($id)
    {
        try {
            $employee = Employee::find($id);

            if (!$employee) {
                throw new Exception('Employee not found');
            }

            $employee->delete();

            return ResponseFormatter::success('Employee deleted');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }
}
