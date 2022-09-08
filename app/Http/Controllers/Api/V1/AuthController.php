<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

use Throwable;

class AuthController extends Controller
{
    /**
     * Store a newly created user in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function createUser(Request $request): JsonResponse
    {
        try {
            //Validated
            $validateUser = Validator::make(
                $request->all(),
                [
                    'name' => 'required',
                    'email' => 'required|email|unique:users,email',
                    'password' => 'required'
                ]
            );

            if ($validateUser->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'isAdmin' => 1,
                'supervisorId' => null,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'User Created Successfully',
                // 'token' => $user->createToken("API TOKEN")->plainTextToken
            ], 200);
        } catch (Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * add new user as employee to the user who logged in.
     *
     * @param Request $request
     * @return JsonResponse
     */

    public function addEmployee(Request $request): JsonResponse
    {
        try {
            if (Project::where('supervisorId', auth()->id())) {

                //Validated
                $validateUser = Validator::make(
                    $request->all(),
                    [
                        'name' => 'required',
                        'email' => 'required|email|unique:users,email',
                        'password' => 'required'
                    ]
                );

                if ($validateUser->fails()) {
                    return response()->json([
                        'status' => false,
                        'message' => 'validation error',
                        'errors' => $validateUser->errors()
                    ], 401);
                }

                $user = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'isAdmin' => 0,
                    'supervisorId' => auth()->id(),
                ]);

                return response()->json([
                    'status' => true,
                    'message' => 'User Created Successfully',
                    // 'token' => $user->createToken("API TOKEN")->plainTextToken
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => "you don't have any project to add employee"
                ]);
            }
        } catch (Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * log in to a user in database and create token.
     *
     * @param Request $request
     * @return JsonResponse
     */

    public function loginUser(Request $request): JsonResponse
    {
        try {
            $validateUser = Validator::make(
                $request->all(),
                [
                    'email' => 'required|email',
                    'password' => 'required'
                ]
            );

            if ($validateUser->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }

            if (!Auth::attempt($request->only(['email', 'password']))) {
                return response()->json([
                    'status' => false,
                    'message' => 'Email & Password does not match with our record.',
                ], 401);
            }

            $user = User::where('email', $request->email)->first();
            $token = $user->createToken("API TOKEN")->plainTextToken;

            return response()->json([
                'status' => true,
                'message' => 'User Logged In Successfully',
                'token' => $token
            ], 200);
        } catch (Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * log out the user by deleting the token .
     *
     * @param Request $request
     * @return JsonResponse|bool
     */
    public function logoutUser(Request $request): JsonResponse|bool
    {
        $user = $request->user();
        if (!$user) {
            return true;
        }
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'status' => 'true',
            'message' => 'Successfully logged out',
        ], 200);
    }
}
