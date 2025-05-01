<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

use App\Models\User;
use App\Models\UserDisabilityType;

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

            ]);



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
                'email' => $request->email,
                'password' => Hash::make($request->password)
            ]);

            $userId = $user->id;
            $disabilityTypeIds = $request->disability_type_ids;

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

            $user = Auth::user()->load('position');

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
            'user' => Auth::user()->load('position')
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
}
