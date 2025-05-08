<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Applicant;
use App\Models\JobPosting;

class DashboardController extends Controller
{
    public function company($company_id)
    {
        try {
            $totalJobPostings = JobPosting::where('company_id', $company_id)->count();


            $totalApplicants = Applicant::whereHas('jobPosting', function ($query) use ($company_id) {
                $query->where('company_id', $company_id);
            })->distinct('user_id')->count('user_id');


            $totalAcceptedApplicants = Applicant::whereHas('jobPosting', function ($query) use ($company_id) {
                $query->where('company_id', $company_id);
            })->where('status', 'Hired')->count();


            $totalRejectedApplicants = Applicant::whereHas('jobPosting', function ($query) use ($company_id) {
                $query->where('company_id', $company_id);
            })->where('status', 'Rejected')->count();


            $dashboard = [
                'job_postings' => $totalJobPostings,
                'applicants' => $totalApplicants,
                'accepted_applicants' => $totalAcceptedApplicants,
                'rejected_applicants' => $totalRejectedApplicants,
            ];

            return response()->json([
                'status' => 'success',
                'message' => 'Company dashboard data retrieved!',
                'data' => $dashboard,
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while fetching company statistics',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
