<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Applicant;
use Exception;

class ApplicantController extends Controller
{

    public function getByJobPosting($job_posting_id, Request $request)
    {
        try {
            $query = Applicant::with('user')
                ->where('job_posting_id', $job_posting_id)
                ->where('active', 1);

            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            if ($request->has('query') && !empty($request->query)) {
                $searchTerm = $request->query;

                $query->whereHas('user', function ($userQuery) use ($searchTerm) {
                    $userQuery->where('lastname', 'LIKE', '%' . $searchTerm . '%')
                        ->orWhere('firstname', 'LIKE', '%' . $searchTerm . '%')
                        ->orWhere('middlename', 'LIKE', '%' . $searchTerm . '%');
                });
            }

            $applicants = $query->orderBy('id', 'desc')->paginate($request->input('per_page', 10));

            return response()->json([
                'status' => 'success',
                'current_page' => $applicants->currentPage(),
                'total_pages' => $applicants->lastPage(),
                'total_items' => $applicants->total(),
                'data' => $applicants->items(),
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
                'user_id' => 'required|exists:users,id',
                'job_posting_id' => 'required|exists:job_postings,id',
            ]);


            $applicant = Applicant::create([
                'user_id' => $request->user_id,
                'job_posting_id' => $request->job_posting_id,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Applicant created successfully',
                'data' => $applicant,
            ], 201);
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
            $applicant = Applicant::with('user')->where('id', $id)->where('active', 1)->firstOrFail();

            return response()->json([
                'status' => 'success',
                'data' => $applicant,
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Applicant not found',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    public function getByUserId($user_id, Request $request)
    {
        try {

            $applicants = Applicant::with('jobPosting')
                ->where('user_id', $user_id)
                ->orderBy('id', 'desc')
                ->get();

            return response()->json([
                'status' => 'success',
                'total_items' => $applicants->count(),
                'data' => $applicants,
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function notifyCount($user_id)
    {
        try {
            $count = Applicant::where('user_id', $user_id)
                ->where('active', 1)
                ->count();

            return response()->json([
                'status' => 'success',
                'count' => $count,
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function seen($user_id)
    {
        try {

            Applicant::where('user_id', $user_id)->updateQuietly(['active' => 0]);

            return response()->json([
                'status' => 'success',
                'message' => 'All applicant records marked as unseen.',
            ], 200);
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
            $request->validate([
                'status' => 'nullable|string',
            ]);

            $applicant = Applicant::findOrFail($id);

            $applicant->update([
                'status' => $request->status,
                'active' => 1,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Applicant status updated successfully',
                'data' => $applicant,
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
            $applicant = Applicant::findOrFail($id);
            $applicant->update(['active' => 0]);

            return response()->json([
                'status' => 'success',
                'message' => 'Applicant deleted successfully',
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getApplicationsByUser($user_id)
    {
        try {

            $applications = Applicant::where('user_id', $user_id)
                ->with(['jobPosting', 'jobPosting.company'])
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $applications,
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while fetching applications',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getApplicantsByJobPosting(Request $request, $job_posting_id)
    {
        try {

            $query = Applicant::where('job_posting_id', $job_posting_id)
                ->with(['user', 'user.disabilityTypes']);

            if ($request->has('status') && $request->status !== '') {
                $query->where('status', $request->status);
            }

            if ($request->has('query') && !empty($request->query)) {
                $searchTerm = $request->query;

                $query->whereHas('user', function ($q) use ($searchTerm) {
                    $q->where('lastname', 'LIKE', '%' . $searchTerm . '%')
                        ->orWhere('firstname', 'LIKE', '%' . $searchTerm . '%')
                        ->orWhere('middlename', 'LIKE', '%' . $searchTerm . '%')
                        ->orWhere('email', 'LIKE', '%' . $searchTerm . '%');
                });
            }

            $applicants = $query->paginate(10);

            return response()->json([
                'status' => 'success',
                'current_page' => $applicants->currentPage(),
                'total_pages' => $applicants->lastPage(),
                'total_items' => $applicants->total(),
                'data' => $applicants->items(),
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while fetching applicants',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
