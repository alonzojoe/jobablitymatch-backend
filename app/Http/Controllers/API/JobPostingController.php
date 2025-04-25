<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\JobPosting;

class JobPostingController extends Controller
{

    public function index(Request $request)
    {
        try {
            $query = JobPosting::with('company');

            if ($request->has('title')) {
                $query->where('title', 'LIKE', '%' . $request->title . '%');
            }

            if ($request->has('description')) {
                $query->where('description', 'LIKE', '%' . $request->description . '%');
            }

            if ($request->has('company_id')) {
                $query->where('company_id', $request->company_id);
            }

            $jobPostings = $query->orderBy('id', 'desc')->paginate($request->input('per_page', 10));

            return response()->json([
                'status' => 'success',
                'current_page' => $jobPostings->currentPage(),
                'total_pages' => $jobPostings->lastPage(),
                'total_items' => $jobPostings->total(),
                'data' => $jobPostings->items(),
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function getByCompany(Request $request, $company_id)
    {
        try {
            $jobPostings = JobPosting::with('company')
                ->where('company_id', $company_id)
                ->orderBy('id', 'desc')
                ->paginate($request->input('per_page', 10));

            return response()->json([
                'status' => 'success',
                'current_page' => $jobPostings->currentPage(),
                'total_pages' => $jobPostings->lastPage(),
                'total_items' => $jobPostings->total(),
                'data' => $jobPostings->items(),
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function show($id)
    {
        try {
            $jobPosting = JobPosting::with('company')->findOrFail($id);

            return response()->json([
                'status' => 'success',
                'data' => $jobPosting,
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Job posting not found',
                'error' => $e->getMessage(),
            ], 404);
        }
    }


    public function store(Request $request)
    {
        try {
            $request->validate([
                'title' => 'required|string',
                'description' => 'required|string',
                'company_id' => 'required|exists:companies,id',
            ]);

            $jobPosting = JobPosting::create([
                'title' => $request->title,
                'description' => $request->description,
                'company_id' => $request->company_id,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Job posting created successfully',
                'data' => $jobPosting,
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
            $jobPosting = JobPosting::findOrFail($id);

            $request->validate([
                'title' => 'nullable|string',
                'description' => 'nullable|string',
            ]);

            $jobPosting->update([
                'title' => $request->title ?? $jobPosting->title,
                'description' => $request->description ?? $jobPosting->description,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Job posting updated successfully',
                'data' => $jobPosting,
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
            $jobPosting = JobPosting::findOrFail($id);
            $jobPosting->update(['status' => 0]);

            return response()->json([
                'status' => 'success',
                'message' => 'Job posting deleted successfully',
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function inactive($id)
    {
        try {
            $jobPosting = JobPosting::findOrFail($id);

            $jobPosting->update(['active' => 0]);

            return response()->json([
                'status' => 'success',
                'message' => 'Job posting marked as inactive successfully',
                'data' => $jobPosting,
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
