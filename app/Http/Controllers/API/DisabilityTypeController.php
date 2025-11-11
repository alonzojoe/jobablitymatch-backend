<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DisabilityType;
use Exception;

class DisabilityTypeController extends Controller
{

    public function all()
    {
        try {

            $disabilityTypes = DisabilityType::where('status', 1)
                ->orderBy('id', 'desc')
                ->get();

            return response()->json([
                'status' => 'success',
                'total_items' => $disabilityTypes->count(),
                'data' => $disabilityTypes,
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
            // $query = DisabilityType::where('status', 1);
            $query = DisabilityType::query();


            if ($request->has('name')) {
                $query->where('name', 'LIKE', '%' . $request->name . '%');
            }


            $disabilityTypes = $query->orderBy('id', 'desc')->paginate($request->input('per_page', 10));

            return response()->json([
                'status' => 'success',
                'current_page' => $disabilityTypes->currentPage(),
                'total_pages' => $disabilityTypes->lastPage(),
                'total_items' => $disabilityTypes->total(),
                'data' => $disabilityTypes->items(),
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
            $disabilityType = DisabilityType::findOrFail($id);

            return response()->json([
                'status' => 'success',
                'data' => $disabilityType,
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Disability type not found',
                'error' => $e->getMessage(),
            ], 404);
        }
    }


    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|unique:disability_types,name',
            ]);

            $disabilityType = DisabilityType::create([
                'name' => $request->name,
                'status' => 1,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Disability type created successfully',
                'data' => $disabilityType,
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
            $disabilityType = DisabilityType::findOrFail($id);

            $request->validate([
                'name' => 'nullable|string|unique:disability_types,name,' . $id,
            ]);

            $disabilityType->update([
                'name' => $request->name ?? $disabilityType->name,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Disability type updated successfully',
                'data' => $disabilityType,
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
            $disabilityType = DisabilityType::findOrFail($id);
            $disabilityType->update(['status' => 0]);

            return response()->json([
                'status' => 'success',
                'message' => 'Disability type deleted successfully',
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
            $disabilityType = DisabilityType::findOrFail($id);

            $disabilityType->update(['status' => $disabilityType->status == 1 ? 0 : 1]);

            $statusText = $disabilityType->status == 1 ? 'activated' : 'deactivated';

            return response()->json([
                'status' => 'success',
                'message' => "Disability Type status updated successfully!",
                'data' => [
                    'user_id' => $disabilityType->id,
                    'status' => $disabilityType->status,
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
