<?php

namespace App\Http\Controllers\Api;

use App\Category;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Role;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Laravel\Passport\Passport;

class AuthController extends Controller
{
    public function imageProcess() {
        $image = request()->avatar_image;
        $email = request()->email;
        if (!$image) {
            File::deleteDirectory(public_path('/images/'.$email));
            return null;
        }

        if (!Str::startsWith($image, 'data:image')) return $image;
        $imageName = null;

        File::deleteDirectory(public_path('/images/'.$email));

        $name = time().'.' . explode('/', explode(':', substr($image, 0, strpos($image, ';')))[1])[1];
        File::makeDirectory(public_path('/images/'.$email), 0777,true,true);
        Image::make($image)->fit(100, 100)->save(public_path('/images/'.$email.'/').$name);
        $imageName = 'images/'.$email.'/'.$name;

        return url($imageName);
    }

    public function register () {

        request()->validate([
            'name' => 'required',
            'avatar_id' => 'required|integer',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed|min:8'
        ]);

        $imageName = $this->imageProcess();

        $user = User::create([
            'name' => request()->name,
            'avatar_id' => request()->avatar_id,
            'avatar_image' => $imageName,
            'email' => request()->email,
            'email_verified_at' => now(),
            'password' => Hash::make(request()->password)
        ]);

        $role = Role::where('name', 'member')->first();
        $user->roles()->attach($role);
        for ($i = 0; $i < 3; $i++) {
            Category::create([
                'user_id' => $user->id,
                'name'=> 'Category' . ($i+1)
            ]);
        }

        $user->sendEmailVerificationNotification();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message'=> "Registration failed"
            ],Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            'success' => true,
            'message'=> "We have sent you an email to validate your email. Please Check your email inbox or spam.",
            'email' => $user->email
        ],Response::HTTP_OK);
    }

    public function login() {

        $user = User::where('email', request()->email)->where('email_verified_at', '<>', NULL)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Email is not verified'
            ],Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $validated = request()->validate([
            'email' => 'required|email|exists:users',
            'password' => 'required'
        ]);

        if (!Auth::attempt($validated)){
            return response()->json([
                'success' => false,
                'message' => 'Incorrect Password'
            ],Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (request()->rememberMe)
            Passport::personalAccessTokensExpireIn(now()->addHours(24));

        $user = request()->user();
        $token = $user->createToken('Personal Access Token')->accessToken;
        $modUser =  new UserResource($user);

        return response()->json([
            'success' => true,
            'token' => $token,
            'user' => $modUser
        ],Response::HTTP_OK);
    }

    public function logout() {
        request()->user()->token()->revoke();
        return response()->json([
            'success' => true,
            'message' => 'The user has been successfully logged out'
        ],Response::HTTP_OK);
    }

    public function getUsers() {
        if (Gate::denies('edit-users')){
            return response()->json([],Response::HTTP_UNAUTHORIZED);
        }

        $users = DB::table('users')
            ->join('role_user', 'role_user.user_id', '=', 'users.id')
            ->select('id', 'avatar_id','avatar_image','name','email','role_id')
            ->get();

        return response()->json([
            'users' => $users
        ],Response::HTTP_OK);
    }

    public function getUser() {
        $user = auth()->user();
        $modUser =  new UserResource($user);

        return response()->json([
            'user' => $modUser
        ],Response::HTTP_OK);
    }

    public function updateUser() {
        $user = auth()->user();
        $getUser = request()->all();

        $imageName = $this->imageProcess();

        $user['name'] = $getUser['name'];
        $user['avatar_id'] = $getUser['avatar_id'];
        $user['avatar_image'] = $imageName;

        $user->update();
        $modUser =  new UserResource($user);

        return response()->json([
            'message' => 'Your profile has been updated.',
            'user' => $modUser
        ],Response::HTTP_OK);
    }

    public function updateThumbUpPlaylist() {
        $user = auth()->user();
        $getUser = request()->all();

        $user['thumb_up_playlist'] = $getUser['thumb_up_playlist'];

        $user->update();
        $modUser =  new UserResource($user);

        return response()->json([
            'message' => '',
            'user' => $modUser
        ],Response::HTTP_OK);
    }

    public function changePassword() {
        $user = auth()->user();
        $getPass = request()->all();

        $currentPassword = $getPass['current'];
        if (!Hash::check($currentPassword, $user->getAuthPassword())) {
            return response()->json([
                'success' => false,
                'message' => 'Incorrect Current Password'
            ],Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $newPassword = $getPass['new'];
        $user['password'] = Hash::make($newPassword);
        $user->update();

        return response()->json([
            'success' => true,
            'message' => 'Your password has been changed.',
        ],Response::HTTP_OK);
    }

    public function update() {
        if (Gate::denies('edit-users')){
            return response()->json([],Response::HTTP_UNAUTHORIZED);
        }
        $getUser = request()->all();
        $user = User::all()->find($getUser['id']);

        $user['name'] = $getUser['name'];
        $user['avatar_id'] = $getUser['avatar_id'];

        $imageName = $this->imageProcess();

        $user['avatar_image'] = $imageName;
        $user->roles()->sync($getUser['role_id']);

        $user->update();

        return response()->json([
            'message' => 'The user data has been updated.',
        ],Response::HTTP_OK);
    }

    public function destroy()
    {
        if (Gate::denies('delete-users')){
            return response()->json([],Response::HTTP_UNAUTHORIZED);
        }

        $user = User::all()->find(request()->id);

        $user->roles()->detach();

        if ($user['avatar_image'])
            File::deleteDirectory(public_path('/images/'.$user['email']));

        if ($user->delete()){
            return response()->json([
                'message' => 'The user has been deleted.',
            ],Response::HTTP_OK);
        }else{
            return response()->json([
                'message' => 'There was an error deleting the user.',
            ],Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
