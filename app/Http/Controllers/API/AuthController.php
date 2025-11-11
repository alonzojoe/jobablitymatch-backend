<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\Company;
use App\Models\UserDisabilityType;
use Cloudinary\Cloudinary;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    public function register(Request $request)
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
                'role_id' => 'nullable|exists:roles,id',
                'email' => 'required|string|email|max:255|unique:users,email',
                'password' => 'required|string|min:6',
                'disability_type_ids' => 'nullable|array',
                'pwdid_picture' => 'nullable|image|mimes:jpeg,png,jpg|max:5120', //5mb
            ]);

            $pwdidPath = null;
            if ($request->role_id == 2 && $request->hasFile('pwdid_picture')) {

                if (env('APP_ENV') === 'local') {
                    $pwdidPath = $request->file('pwdid_picture')->store('pwdid_pictures', 'public');
                } else {
                    $pwdidPicture = $request->file('pwdid_picture');
                    $cloudinary = new Cloudinary(config('services.cloudinary'));
                    $uploadedImage = $cloudinary->uploadApi()->upload($pwdidPicture->getRealPath(), [
                        'folder' => 'pwdid_pictures',
                    ]);
                    $pwdidPath = $uploadedImage['secure_url'];
                }
            }

            $user = User::create([
                'firstname' => strtoupper($request->firstname),
                'lastname' => strtoupper($request->lastname),
                'middlename' => strtoupper($request->middlename),
                'birthdate' => $request->birthdate,
                'gender' => strtoupper($request->gender),
                'address' => strtoupper($request->address),
                'phone' => $request->phone,
                'pwd_id_no' => $request->pwd_id_no,
                'role_id' => $request->role_id,
                'pwdid_path' => $pwdidPath,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'company' => 'nullable|string|unique:companies,name',
                'company_address' => 'nullable|string',
            ]);

            $userId = $user->id;
            $roleId = $request->role_id;
            $disabilityTypeIds = $request->disability_type_ids;

            if ($roleId == 2) {
                if (!empty($disabilityTypeIds)) {
                    UserDisabilityType::where('user_id', $userId)->delete();
                    foreach ($disabilityTypeIds as $disabilityTypeId) {
                        UserDisabilityType::create([
                            'user_id' => $userId,
                            'disability_type_id' => $disabilityTypeId,
                            'status' => 1,
                        ]);
                    }
                }
            } else if ($roleId == 3) {
                Company::create([
                    'name' => strtoupper($request->company),
                    'address' => strtoupper($request->company_address) ?? '',
                    'user_id' => $userId,
                    'status' => 1,
                ]);
            }



            $token = Auth::login($user);

            return response()->json([
                'status' => 'success',
                'message' => 'User Created Successfully!',
                'user' => $user,
                'authorization' => [
                    'token' => $token,
                    'type' => 'Bearer'
                ]
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
    }

    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|string|email',
                'password' => 'required|string'
            ]);

            $credentials = $request->only('email', 'password');
            $user = User::where('email', $credentials['email'])->where('status', 1)->first();

            if (!$user || !$token = Auth::attempt($credentials)) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $user = Auth::user()->load('role')->load('company')->load('disabilityTypes');

            return response()->json([
                'status' => 'success',
                'user' => $user,
                'authorization' => [
                    'token' => $token,
                    'type' => 'Bearer'
                ]
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function logout()
    {
        Auth::logout();
        return response()->json([
            'status',
            'success',
            'message',
            'User logged out.'
        ], 200);
    }

    public function me()
    {
        return response()->json([
            'status' => 'success',
            'user' => Auth::user()->load('role')->load('company')->load('disabilityTypes')
        ], 200);
    }

    public function refresh()
    {
        return response()->json([
            'status' => 'success',
            'message' => 'refresh token generated',
            'user' => Auth::user(),
            'authorization' => [
                'token' => Auth::refresh(),
                'type' => 'Bearer'
            ]
        ], 200);
    }

    public function changePassword(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|string|email|exists:users,email',
                'oldpassword' => 'required|string',
                'newpassword' => 'required|string|min:6',
            ]);


            $user = User::where('email', $request->email)->first();


            if (!Hash::check($request->oldpassword, $user->password)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Old password is incorrect'
                ], 400);
            }


            $user->password = Hash::make($request->newpassword);
            $user->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Password changed successfully'
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function resetPassword(Request $request)
    {
        try {

            $request->validate([
                'email' => 'required|string|email|exists:users,email',
            ]);

            $user = User::where('email', $request->email)->first();

            if (!$user->birthdate) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User birthdate not found. Cannot reset password.'
                ], 400);
            }

            $birthdate = \Carbon\Carbon::parse($user->birthdate);
            $newPassword = $birthdate->format('mdY');

            $user->password = Hash::make($newPassword);
            $user->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Password reset successfully',
                'new_password' => $newPassword
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function getCloudinaryPublicId($url)
    {
        if (strpos($url, 'cloudinary.com') !== false) {
            // Extract public_id from Cloudinary URL
            preg_match('/\/v\d+\/(.+)\.\w+$/', $url, $matches);
            return $matches[1] ?? null;
        }
        return null;
    }
}
