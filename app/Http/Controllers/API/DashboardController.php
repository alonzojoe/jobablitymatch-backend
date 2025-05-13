<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Applicant;
use App\Models\JobPosting;
use App\Models\User;
use App\Models\Company;

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

    public function admin()
    {
        try {

            $totalUsers = User::count();
            $totalCompanies = Company::count();
            $totalJobPostings = JobPosting::count();
            $totalApplicants = Applicant::count();

            $recentUsers = User::with(['role', 'disabilityTypes', 'company'])
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();

            $recentCompanies = Company::with(['user', 'jobPostings'])
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();

            $recentJobPostings = JobPosting::with(['company', 'disabilityTypes'])
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();

            $recentApplicants = Applicant::with(['user', 'jobPosting'])
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();

            $dashboard = [
                'total_users' => $totalUsers,
                'total_companies' => $totalCompanies,
                'total_job_postings' => $totalJobPostings,
                'total_applicants' => $totalApplicants,
                'recent_users' => $recentUsers,
                'recent_companies' => $recentCompanies,
                'recent_job_postings' => $recentJobPostings,
                'recent_applicants' => $recentApplicants,
            ];

            return response()->json([
                'status' => 'success',
                'message' => 'Admin dashboard data retrieved!',
                'data' => $dashboard,
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while fetching admin statistics',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
