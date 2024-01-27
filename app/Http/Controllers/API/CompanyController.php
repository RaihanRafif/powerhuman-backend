<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateCompanyRequest;
use App\Http\Requests\updateCompanyRequest;
use App\Models\Company;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanyController extends Controller
{
    public function fetch(Request $request)
    {
        $id = $request->input('id');
        $limit = $request->input('limit', 10);
        $name = $request->input('name');

        if ($id) {
            $company = Company::with(['users'])->whereHas('users', function ($query) {
                $query->where('user_id', Auth::id());
            })->find($id);


            if ($company) {
                return ResponseFormatter::success($company, 'Company found');
            }

            return ResponseFormatter::error('Company not found', 404);
        }

        $companies = Company::with(['users'])->whereHas('users', function ($query) {
            $query->where('user_id', Auth::id());
        });

        if ($name) {
            $companies->where('name', 'like', '%' . $name . '%');
        }

        return ResponseFormatter::success(
            $companies->paginate($limit),
            'Companies Found'
        );
    }

    public function create(CreateCompanyRequest $request)
    {
        try {
            $path = null;
            if ($request->hasFile('logo')) {
                $path = $request->file('logo')->store('public/logos');
            }

            $company = Company::create([
                'name' => $request->name,
                'logo' => $path
            ]);

            if (!$company) {
                throw  new Exception('Company not created');
            }

            $user = User::find(Auth::id());
            $user->companies()->attach($company->id);

            $company->load('users');

            return ResponseFormatter::success($company, 'Company Created');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage());
        }
    }

    public function update(updateCompanyRequest $req, $id)
    {
        try {
            $company = Company::find($id);

            if (!$company) {
                throw new Exception('Company not found');
            }

            // Initialize an array to hold the update data
            $updateData = ['name' => $req->name];

            // Check if a new logo file was uploaded
            if ($req->hasFile('logo')) {
                $path = $req->file('logo')->store('public/logos');
                // Add the new logo path to the update data array
                $updateData['logo'] = $path;
            }

            // Update the company with the new data (name and possibly logo)
            $company->update($updateData);

            return ResponseFormatter::success($company, 'Company Updated');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }
}

// try {

// } catch (Exception $e) {

// }
