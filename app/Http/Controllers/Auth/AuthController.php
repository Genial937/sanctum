<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $this->validate($request, [
            'name' => ['required', 'string', 'max:191'],
            'email' => ['required', 'email', 'max:191', 'unique:users,email'],
            'password' => ['required']
        ]);
        $user = User::create(array_merge($request->all(), ['password' => Hash::make($data['password'])]));
        $token = $user->createToken('authentictoken')->plainTextToken;
        $result = [
            'status' => 200,
            'token' => $token,
            'user_details' => $user
        ];
        return response()->json($result);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email', 'max:191'],
            'password' => ['required']
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 404);
        }

        $user = User::where('email', $validator['email'])->first();
        if ($user) {
            //check password
            if (Auth::attempt(['email' => $validator['email'], 'password' => $validator['password']])) {
                # code...$token = $user->createToken('authentictoken')->plainTextToken;
                $result = [
                    'status' => 200,
                    'token' => $token,
                    'user_details' => $user
                ];
                return response()->json($result);
            } else {
                $result = [
                    'status' => 404,
                    'message' => 'Invalid Password'
                ];
                return response()->json($result);
            }
        } else {
            $result = [
                'status' => 404,
                'message' => 'User account does not exist'
            ];
            return response()->json($result);
        }
    }

    public function logout()
    {
        Auth::user()->tokens()->delete();
        $result = [
            'status' => 200,
            'message' => 'Logout successful!',
        ];
        return response()->json($result);
    }
}
