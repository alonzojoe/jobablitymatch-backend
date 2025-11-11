<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserDisabilityType;
use App\Models\Company;
use Illuminate\Support\Facades\Storage;
use Cloudinary\Cloudinary;
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
                'pwdid_picture' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            ]);

            if ($request->role_id == 2 && $request->hasFile('pwdid_picture')) {

                if (env('APP_ENV') === 'local') {
                    if ($user->pwdid_path && Storage::disk('public')->exists($user->pwdid_path)) {
                        Storage::disk('public')->delete($user->pwdid_path);
                    }


                    $pwdidPath = $request->file('pwdid_picture')->store('pwdid_pictures', 'public');
                    $user->pwdid_path = $pwdidPath;
                } else {
                    if ($user->pwdid_path && strpos($user->pwdid_path, 'cloudinary.com') !== false) {
                        $publicId = $this->getCloudinaryPublicId($user->pwdid_path);
                        if ($publicId) {
                            $cloudinary = new Cloudinary(config('services.cloudinary'));
                            $cloudinary->uploadApi()->destroy($publicId);
                        }
                    }

                    $pwdidPicture = $request->file('pwdid_picture');
                    $cloudinary = new Cloudinary(config('services.cloudinary'));
                    $uploadedImage = $cloudinary->uploadApi()->upload($pwdidPicture->getRealPath(), [
                        'folder' => 'pwdid_pictures',
                    ]);

                    $user->pwdid_path = $uploadedImage['secure_url'];
                }
            }

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
                'pwdid_picture' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            ]);

            $user = User::findOrFail($user_id);

            if ($request->hasFile('pwdid_picture')) {

                if (env('APP_ENV') === 'local') {

                    if ($user->pwdid_path && Storage::disk('public')->exists($user->pwdid_path)) {
                        Storage::disk('public')->delete($user->pwdid_path);
                    }

                    $pwdidPath = $request->file('pwdid_picture')->store('pwdid_pictures', 'public');
                    $user->pwdid_path = $pwdidPath;
                } else {

                    if ($user->pwdid_path && strpos($user->pwdid_path, 'cloudinary.com') !== false) {
                        $publicId = $this->getCloudinaryPublicId($user->pwdid_path);
                        if ($publicId) {
                            $cloudinary = new Cloudinary(config('services.cloudinary'));
                            $cloudinary->uploadApi()->destroy($publicId);
                        }
                    }


                    $pwdidPicture = $request->file('pwdid_picture');
                    $cloudinary = new Cloudinary(config('services.cloudinary'));
                    $uploadedImage = $cloudinary->uploadApi()->upload($pwdidPicture->getRealPath(), [
                        'folder' => 'pwdid_pictures',
                    ]);

                    $user->pwdid_path = $uploadedImage['secure_url'];
                }
            }

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

    public function activeInactive($userID)
    {

        try {
            $user = User::findOrFail($userID);

            $user->update(['status' => $user->status == 1 ? 0 : 1]);

            $statusText = $user->status == 1 ? 'activated' : 'deactivated';

            return response()->json([
                'status' => 'success',
                'message' => "User status updated successfully!",
                'data' => [
                    'user_id' => $user->id,
                    'status' => $user->status,
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

    private function getCloudinaryPublicId($url)
    {
        if (strpos($url, 'cloudinary.com') !== false) {
            preg_match('/\/v\d+\/(.+)\.\w+$/', $url, $matches);
            return $matches[1] ?? null;
        }
        return null;
    }
}
