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
            // $query = Company::with(['user', 'jobPostings'])->withCount('jobPostings')->where('status', 1);
            $query = Company::with(['user', 'jobPostings'])->withCount('jobPostings');
            if ($request->has('name')) {
                $query->where('name', 'LIKE', '%' . $request->name . '%');
            }

            if ($request->has('query') && !empty($request->query)) {
                $searchTerm = $request->query;

                $query->where(function ($q) use ($searchTerm) {
                    $q->where('name', 'LIKE', '%' . $searchTerm . '%')
                        ->orWhere('address', 'LIKE', '%' . $searchTerm . '%')

                        ->orWhereHas('user', function ($userQuery) use ($searchTerm) {
                            $userQuery->where('lastname', 'LIKE', '%' . $searchTerm . '%')
                                ->orWhere('firstname', 'LIKE', '%' . $searchTerm . '%')
                                ->orWhere('middlename', 'LIKE', '%' . $searchTerm . '%');
                        });
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
            $company = Company::with([
                'user',
                'jobPostings' => function ($query) {
                    $query->with(['company', 'disabilityTypes']);
                }
            ])->withCount('jobPostings')->find($company_id);


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

    public function activeInactive($id)
    {
        try {
            $company = Company::findOrFail($id);

            $company->update(['status' => $company->status == 1 ? 0 : 1]);

            $statusText = $company->status == 1 ? 'activated' : 'deactivated';

            return response()->json([
                'status' => 'success',
                'message' => "Company status updated successfully!",
                'data' => [
                    'user_id' => $company->id,
                    'status' => $company->status,
                    'message' => $statusText
                ]
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
