<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserDisabilityType;
use App\Models\Company;
use Exception;


class UserController extends Controller
{

    public function index(Request $request)
    {
        try {
            $query = User::with(['role', 'company', 'disabilityTypes'])->where('status', 1);

            if ($request->has('role_id') && $request->role_id != 0) {
                $query->whereHas('role', function ($q) use ($request) {
                    $q->where('id', $request->role_id);
                });
            }

            if ($request->has('lastname')) {
                $query->where('lastname', 'LIKE', '%' . $request->lastname . '%');
            }

            if ($request->has('firstname')) {
                $query->where('firstname', 'LIKE', '%' . $request->firstname . '%');
            }

            if ($request->has('middlename')) {
                $query->where('middlename', 'LIKE', '%' . $request->middlename . '%');
            }

            if ($request->has('email')) {
                $query->where('email', 'LIKE', '%' . $request->email . '%');
            }

            if ($request->has('pwd_id_no')) {
                $query->where('pwd_id_no', 'LIKE', '%' . $request->pwd_id_no . '%');
            }

            $users = $query->orderBy('id', 'desc')->paginate($request->input('per_page', 10));

            return response()->json([
                'status' => 'success',
                'current_page' => $users->currentPage(),
                'total_pages' => $users->lastPage(),
                'total_items' => $users->total(),
                'data' => $users->items(),
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

            $user = User::with(['role', 'company', 'disabilityTypes'])->findOrFail($id);

            return response()->json([
                'status' => 'success',
                'data' => $user,
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);

            $request->validate([
                'firstname' => 'nullable|string',
                'lastname' => 'nullable|string',
                'middlename' => 'nullable|string',
                'birthdate' => 'nullable|date',
                'gender' => 'nullable|string',
                'address' => 'nullable|string',
                'phone' => 'nullable|string',
                'pwd_id_no' => 'nullable|string',
                'role_id' => 'nullable|exists:roles,id',
                'disability_type_ids' => 'nullable|array',
            ]);


            $user->update([
                'firstname' => $request->firstname ?? $user->firstname,
                'lastname' => $request->lastname ?? $user->lastname,
                'middlename' => $request->middlename ?? $user->middlename,
                'birthdate' => $request->birthdate ?? $user->birthdate,
                'gender' => $request->gender ?? $user->gender,
                'address' => $request->address ?? $user->address,
                'phone' => $request->phone ?? $user->phone,
                'pwd_id_no' => $request->pwd_id_no ?? $user->pwd_id_no,
                'role_id' => $request->role_id ?? $user->role_id,
            ]);


            $disabilityTypeIds = $request->disability_type_ids;
            if (!empty($disabilityTypeIds)) {
                UserDisabilityType::where('user_id', $user->id)->delete();
                foreach ($disabilityTypeIds as $disabilityTypeId) {
                    UserDisabilityType::create([
                        'user_id' => $user->id,
                        'disability_type_id' => $disabilityTypeId,
                        'status' => 1,
                    ]);
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => 'User updated successfully',
                'data' => $user,
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
            $user = User::findOrFail($id);
            $user->update(['status' => 0]);

            return response()->json([
                'status' => 'success',
                'message' => 'User deleted successfully',
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function updatePWDUser(Request $request, $user_id)
    {
        try {

            $request->validate([
                'firstname' => 'nullable|string',
                'lastname' => 'nullable|string',
                'middlename' => 'nullable|string',
                'birthdate' => 'nullable|date',
                'gender' => 'nullable|string',
                'address' => 'nullable|string',
                'phone' => 'nullable|string',
                'pwd_id_no' => 'nullable|string',
                'disability_type_ids' => 'nullable|array',
            ]);

            $user = User::findOrFail($user_id);
            $user->update([
                'firstname' => strtoupper($request->firstname),
                'lastname' => strtoupper($request->lastname),
                'middlename' => strtoupper($request->middlename),
                'birthdate' => $request->birthdate,
                'gender' => strtoupper($request->gender),
                'address' => strtoupper($request->address),
                'phone' => $request->phone,
                'pwd_id_no' => $request->pwd_id_no,
            ]);


            if (!empty($request->disability_type_ids)) {
                UserDisabilityType::where('user_id', $user_id)->delete();
                foreach ($request->disability_type_ids as $disabilityTypeId) {
                    UserDisabilityType::create([
                        'user_id' => $user_id,
                        'disability_type_id' => $disabilityTypeId,
                        'status' => 1,
                    ]);
                }
            }


            $updatedUser = User::with(['role', 'disabilityTypes'])->findOrFail($user_id);

            return response()->json([
                'status' => 'success',
                'message' => 'PWD User Updated Successfully!',
                'user' => $updatedUser,
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while updating PWD user data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateEmployer(Request $request, $user_id)
    {
        try {

            $request->validate([
                'firstname' => 'nullable|string',
                'lastname' => 'nullable|string',
                'middlename' => 'nullable|string',
                'birthdate' => 'nullable|date',
                'gender' => 'nullable|string',
                'address' => 'nullable|string',
                'phone' => 'nullable|string',
                'company' => 'nullable|string|unique:companies,name,' . $user_id . ',user_id',
                'company_address' => 'nullable|string',
            ]);


            $user = User::findOrFail($user_id);
            $user->update([
                'firstname' => strtoupper($request->firstname),
                'lastname' => strtoupper($request->lastname),
                'middlename' => strtoupper($request->middlename),
                'birthdate' => $request->birthdate,
                'gender' => strtoupper($request->gender),
                'address' => strtoupper($request->address),
                'phone' => $request->phone,
            ]);


            $company = Company::where('id', $request->company_id)->first();
            if ($company) {
                $company->update([
                    'name' => strtoupper($request->company),
                    'address' => strtoupper($request->company_address),
                ]);
            }


            $updatedUser = User::with(['role', 'company', 'disabilityTypes'])->findOrFail($user_id);

            return response()->json([
                'status' => 'success',
                'message' => 'Employer Updated Successfully!',
                'user' => $updatedUser,
                'company' => $company,
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while updating employer data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateUser(Request $request, $user_id)
    {
        try {

            $request->validate([
                'firstname' => 'nullable|string',
                'lastname' => 'nullable|string',
                'middlename' => 'nullable|string',
                'birthdate' => 'nullable|date',
                'gender' => 'nullable|string',
                'address' => 'nullable|string',
                'phone' => 'nullable|string',
            ]);


            $user = User::findOrFail($user_id);
            $user->update([
                'firstname' => strtoupper($request->firstname),
                'lastname' => strtoupper($request->lastname),
                'middlename' => strtoupper($request->middlename),
                'birthdate' => $request->birthdate,
                'gender' => strtoupper($request->gender),
                'address' => strtoupper($request->address),
                'phone' => $request->phone,
            ]);



            $updatedUser = User::with(['role', 'company', 'disabilityTypes'])->findOrFail($user_id);

            return response()->json([
                'status' => 'success',
                'message' => 'Employer Updated Successfully!',
                'user' => $updatedUser,
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while updating employer data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
