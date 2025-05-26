<?php

namespace App\Http\Controllers;

use id;
use App\Models\User;
use App\Http\Middleware;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['login', 'register']);
        $this->middleware('admin')->only(['getAllUsers', 'getUser', 'updateUser', 'deleteUser']);
    }

    // Authentication Login 
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Pastikan anda memasukkan email dan password yang benar.'],
            ]);
        }

        $token = $user->createToken($request->device_name)->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Selamat datang nak, bekerjalah yang rajin, walau tak kaya-kaya',
            'user' => [
                'nama' => $user->nama,
                'role' => $user->role
            ],
            'token' => $token
        ], 200);
    }

    // Registrasi / Pendaftaran Akun 
    public function register(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:55',
            'username' => 'required|string|unique:users',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'required|in:admin,kasir'
        ]);

        $user = User::create([
            'nama' => $request->nama,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role
        ]);

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Selamat bergabung! Semoga sukses selalu 😊',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'nama' => $user->nama,
                    'username' => $user->username,
                    'email' => $user->email,
                    'role' => $user->role,
                    'created_at' => $user->created_at
                ],
                'token' => $token
            ]
        ], 201);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Selamat istirahat, sampai jumpa lagi! 👋'
        ]);
    }

    // Tampilkan Profile Users yang sedang Login
    public function getProfile(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'status' => 'success',
            'message' => 'Data profile berhasil diambil',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'nama' => $user->nama,
                    'username' => $user->username,
                    'email' => $user->email,
                    'role' => $user->role,
                    'created_at' => $user->created_at
                ]
            ]
        ]);
    }
    //  Update Profile users 
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'nama' => 'string|max:55',
            'username' => 'string|unique:users,username,'.$user->id,
            'email' => 'string|email|unique:users,email,'.$user->id,
            'password' => 'nullable|string|min:6'
        ]);

        $updates = $request->only(['nama', 'username', 'email']);

        if ($request->filled('password')) {
            $updates['password'] = Hash::make($request->password);
        }

        $user->update($updates);

        return response()->json([
            'status' => 'success',
            'message' => 'Profile berhasil diperbarui',
            'data' => [
                'user' => $user->only(['id', 'nama', 'username', 'email', 'role', 'updated_at'])
            ]
        ]);
    }

    //  Ganti / Tukar Password 
    public function changePassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:6'
        ]);

        $user = $request->user();
        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Password berhasil diperbarui! 🔐',
            'data' => [
                'user' => $user->only(['id', 'nama', 'email', 'updated_at'])
            ]
        ]);
    }
        // Fitur Khusus Admin 
    // // ADMIN: GET ALL USERS
    // public function getAllUsers()
    // {
    //     $users = User::all();
    //     return response()->json($users);
    // }

    // // ADMIN: GET USER BY ID
    // public function getUser($id)
    // {
    //     $user = User::find($id);
    //     if (!$user) return response()->json(['message' => 'User not found'], 404);

    //     return response()->json($user);
    // }

    // // ADMIN: UPDATE USER
    // public function updateUser(Request $request, $id)
    // {
    //     $user = User::find($id);
    //     if (!$user) return response()->json(['message' => 'User not found'], 404);

    //     $validator = Validator::make($request->all(), [
    //         'nama' => 'sometimes|string|max:255',
    //         'username' => 'sometimes|string|unique:users,username,' . $user->id,
    //         'email' => 'sometimes|email|unique:users,email,' . $user->id,
    //         'role' => 'sometimes|in:admin,kasir'
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json($validator->errors(), 422);
    //     }

    //     $user->update($request->only('nama', 'username', 'email', 'role'));

    //     return response()->json(['message' => 'User updated', 'user' => $user]);
    // }

    // // ADMIN: DELETE USER
    // public function deleteUser($id)
    // {
    //     $user = User::find($id);
    //     if (!$user) return response()->json(['message' => 'User not found'], 404);

    //     $user->delete();

    //     return response()->json(['message' => 'User deleted']);
    // }
}
