<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\JobPosting;
use App\Models\JobPostingDisabilityType;
use App\Models\User;

class JobPostingController extends Controller
{

    public function list(Request $request)
    {
        try {
            $query = JobPosting::with(['company', 'disabilityTypes'])
                ->where('status', 1);


            if ($request->has('query') && !empty($request->query)) {
                $searchTerm = $request->query;

                $query->where(function ($q) use ($searchTerm) {
                    $q->where('title', 'LIKE', '%' . $searchTerm . '%')
                        ->orWhere('description', 'LIKE', '%' . $searchTerm . '%');
                });
            }


            if ($request->filled('active') && $request->active != 0) {
                $query->where('active', $request->active);
            }

            if ($request->filled('company_id')) {
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


    public function index(Request $request)
    {
        try {
            $query = JobPosting::with(['company', 'disabilityTypes'])
                ->where('status', 1);

            if ($request->has('searchQuery')) {
                $searchQuery = '%' . $request->searchQuery . '%';
                $query->where(function ($q) use ($searchQuery) {
                    $q->where('title', 'LIKE', $searchQuery)
                        ->orWhere('description', 'LIKE', $searchQuery)
                        ->orWhereHas('company', function ($companyQuery) use ($searchQuery) {
                            $companyQuery->where('name', 'LIKE', $searchQuery);
                        });
                });
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

    public function recommended(Request $request, $user_id)
    {
        try {
            $user = User::with('disabilityTypes')->findOrFail($user_id);
            $userDisabilityTypeIds = $user->disabilityTypes->pluck('id');


            // return response()->json(['data' => $userDisabilityTypeIds], 200);

            $query = JobPosting::with(['company', 'disabilityTypes'])
                ->where('status', 1);


            if ($request->has('searchQuery')) {
                $searchQuery = '%' . $request->searchQuery . '%';
                $query->where(function ($q) use ($searchQuery) {
                    $q->where('job_postings.title', 'LIKE', $searchQuery)
                        ->orWhere('job_postings.description', 'LIKE', $searchQuery)
                        ->orWhereHas('company', function ($companyQuery) use ($searchQuery) {
                            $companyQuery->where('companies.name', 'LIKE', $searchQuery);
                        });
                });
            }


            if ($request->has('company_id')) {
                $query->where('job_postings.company_id', $request->company_id);
            }


            // if ($userDisabilityTypeIds->isNotEmpty()) {
            $query->whereHas('disabilityTypes', function ($q) use ($userDisabilityTypeIds) {
                $q->whereIn('disability_types.id', $userDisabilityTypeIds);
            });
            // } else {

            // }


            $jobPostings = $query->orderBy('job_postings.id', 'desc')->paginate($request->input('per_page', 10));

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

    // public function recommended(Request $request, $user_id)
    // {
    //     try {

    //         $user = User::with('disabilityTypes')->findOrFail($user_id);


    //         $userDisabilityTypeIds = $user->disabilityTypes->pluck('id');

    //         $query = JobPosting::with(['company', 'disabilityTypes']);


    //         if ($request->has('searchQuery')) {
    //             $searchQuery = '%' . $request->searchQuery . '%';
    //             $query->where(function ($q) use ($searchQuery) {
    //                 $q->where('title', 'LIKE', $searchQuery)
    //                     ->orWhere('description', 'LIKE', $searchQuery)
    //                     ->orWhereHas('company', function ($companyQuery) use ($searchQuery) {
    //                         $companyQuery->where('name', 'LIKE', $searchQuery);
    //                     });
    //             });
    //         }


    //         if ($request->has('company_id')) {
    //             $query->where('company_id', $request->company_id);
    //         }


    //         if ($userDisabilityTypeIds->isNotEmpty()) {
    //             $query->whereHas('disabilityTypes', function ($q) use ($userDisabilityTypeIds) {
    //                 $q->whereIn('id', $userDisabilityTypeIds);
    //             });
    //         }

    //         $jobPostings = $query->orderBy('id', 'desc')->paginate($request->input('per_page', 10));

    //         return response()->json([
    //             'status' => 'success',
    //             'current_page' => $jobPostings->currentPage(),
    //             'total_pages' => $jobPostings->lastPage(),
    //             'total_items' => $jobPostings->total(),
    //             'data' => $jobPostings->items(),
    //         ], 200);
    //     } catch (\Throwable $e) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'An error occurred',
    //             'error' => $e->getMessage(),
    //         ], 500);
    //     }
    // }

    // public function index(Request $request)
    // {
    //     try {
    //         $query = JobPosting::with('company');

    //         if ($request->has('title')) {
    //             $query->where('title', 'LIKE', '%' . $request->title . '%');
    //         }

    //         if ($request->has('description')) {
    //             $query->where('description', 'LIKE', '%' . $request->description . '%');
    //         }

    //         if ($request->has('company_id')) {
    //             $query->where('company_id', $request->company_id);
    //         }

    //         $jobPostings = $query->orderBy('id', 'desc')->paginate($request->input('per_page', 10));

    //         return response()->json([
    //             'status' => 'success',
    //             'current_page' => $jobPostings->currentPage(),
    //             'total_pages' => $jobPostings->lastPage(),
    //             'total_items' => $jobPostings->total(),
    //             'data' => $jobPostings->items(),
    //         ], 200);
    //     } catch (\Throwable $e) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'An error occurred',
    //             'error' => $e->getMessage(),
    //         ], 500);
    //     }
    // }


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
                'title' => 'nullable|string',
                'description' => 'nullable|string',
                'vacant_positions' => 'nullable|integer',
                'company_id' => 'nullable|integer',
                'disability_type_ids' => 'nullable|array',
                'hiring_from' => 'nullable|date',
                'hiring_to' => 'nullable|date',
            ]);


            $jobPosting = JobPosting::create([
                'title' => $request->title,
                'description' => $request->description,
                'company_id' => $request->company_id,
                'vacant_positions' => $request->vacant_positions,
                'hiring_from' => $request->hiring_from,
                'hiring_to' => $request->hiring_to,
            ]);


            $disabilityTypeIds = $request->disability_type_ids;
            if (!empty($disabilityTypeIds)) {

                JobPostingDisabilityType::where('job_posting_id', $jobPosting->id)->delete();

                foreach ($disabilityTypeIds as $disabilityTypeId) {
                    JobPostingDisabilityType::create([
                        'job_posting_id' => $jobPosting->id,
                        'disability_type_id' => $disabilityTypeId,
                        'status' => 1,
                    ]);
                }
            }

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
                'vacant_positions' => 'nullable|integer',
                'disability_type_ids' => 'nullable|array',
                'hiring_from' => 'nullable|date',
                'hiring_to' => 'nullable|date',
            ]);


            $jobPosting->update([
                'title' => $request->title ?? $jobPosting->title,
                'description' => $request->description ?? $jobPosting->description,
                'vacant_positions' =>  $request->vacant_positions ?? $jobPosting->vacant_positions,
                'hiring_from' => $request->hiring_from ?? $jobPosting->hiring_from,
                'hiring_to' => $request->hiring_to ?? $jobPosting->hiring_to,
            ]);


            $disabilityTypeIds = $request->disability_type_ids;
            if (!empty($disabilityTypeIds)) {

                JobPostingDisabilityType::where('job_posting_id', $jobPosting->id)->delete();


                foreach ($disabilityTypeIds as $disabilityTypeId) {
                    JobPostingDisabilityType::create([
                        'job_posting_id' => $jobPosting->id,
                        'disability_type_id' => $disabilityTypeId,
                        'status' => 1,
                    ]);
                }
            }

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

    public function activeinactive(Request $request, $id)
    {
        $payload = $request->active;
        try {
            $jobPosting = JobPosting::findOrFail($id);

            $jobPosting->update(['active' => $payload]);

            return response()->json([
                'status' => 'success',
                'message' => 'Job posting updated successfully.',
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
