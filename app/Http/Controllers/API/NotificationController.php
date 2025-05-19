<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Applicant;

class NotificationController extends Controller
{
    public function count($user_id)
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
}
