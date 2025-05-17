<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Company;
use Exception;

class CompanyController extends Controller
{

    public function index(Request $request)
    {
        try {
            $query = Company::with(['user', 'jobPostings'])->withCount('jobPostings')->where('status', 1);

            if ($request->has('name')) {
                $query->where('name', 'LIKE', '%' . $request->name . '%');
            }

            if ($request->has('address')) {
                $query->where('address', 'LIKE', '%' . $request->address . '%');
            }


            if ($request->has('lastname')) {
                $query->whereHas('user', function ($userQuery) use ($request) {
                    $userQuery->where('lastname', 'LIKE', '%' . $request->lastname . '%');
                });
            }

            if ($request->has('firstname')) {
                $query->whereHas('user', function ($userQuery) use ($request) {
                    $userQuery->where('firstname', 'LIKE', '%' . $request->firstname . '%');
                });
            }

            if ($request->has('middlename')) {
                $query->whereHas('user', function ($userQuery) use ($request) {
                    $userQuery->where('middlename', 'LIKE', '%' . $request->middlename . '%');
                });
            }

            $companies = $query->orderBy('id', 'desc')->paginate($request->input('per_page', 10));

            return response()->json([
                'status' => 'success',
                'current_page' => $companies->currentPage(),
                'total_pages' => $companies->lastPage(),
                'total_items' => $companies->total(),
                'data' => $companies->items(),
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function show($company_id)
    {
        try {
            $company = Company::with(['user', 'jobPostings'])->withCount('jobPostings')->find($company_id);

            if (!$company) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Company not found',
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => $company,
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'nullable|string|unique:companies,name',
                'address' => 'nullable|string',
                'user_id' => 'nullable|exists:users,id',
            ]);

            $company = Company::create([
                'name' => $request->name,
                'address' => $request->address ?? '',
                'user_id' => $request->user_id,
                'status' => 1,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Company created successfully',
                'data' => $company,
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function update(Request $request, $id)
    {
        try {
            $company = Company::findOrFail($id);

            $request->validate([
                'name' => 'nullable|string|unique:companies,name,' . $id,
                'address' => 'nullable|string',
            ]);

            $company->update([
                'name' => $request->name ?? $company->name,
                'address' => $request->address ?? $company->address,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Company updated successfully',
                'data' => $company,
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function destroy($id)
    {
        try {
            $company = Company::findOrFail($id);
            $company->update(['status' => 0]);

            return response()->json([
                'status' => 'success',
                'message' => 'Company deleted successfully',
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
