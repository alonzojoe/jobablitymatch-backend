<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Role;
use Exception;

class RoleController extends Controller
{

    public function index()
    {
        try {
            $role = Role::where('status', 1)->get();
            return response()->json(['status' => 'success', 'data' => $role,], 200);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => 'An error occurred', 'error' => $e->getMessage(),], 500);
        }
    }


    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|unique:roles,name',
            ]);

            $role = Role::create([
                'name' => $request->name,
                'status' => 1,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Role created successfully',
                'data' => $role,
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
            $role = Role::findOrFail($id);

            $request->validate([
                'name' => 'string|unique:roles,name,' . $id,
            ]);

            $role->update([
                'name' => $request->name ?? $role->name,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Role updated successfully',
                'data' => $role,
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
            $role = Role::findOrFail($id);
            $role->update(['status' => 0]);

            return response()->json([
                'status' => 'success',
                'message' => 'Role deleted successfully',
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
