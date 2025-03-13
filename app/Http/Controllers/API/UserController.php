<?php

namespace App\Http\Controllers\API;

use App\Mail\VerifyEmail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class UserController extends BaseController
{
    public function getUser() {
        $authUser = Auth::user();
        $user = User::findOrFail($authUser->id);
        $user->avatar = $this->getS3Url($user->avatar);
        return $this->sendResponse($user, 'User');
        }

        /**
     * Upload and update user's avatar.
     */
    public function uploadAvatar(Request $request)
    {
        // Validate the uploaded image
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg',
        ]);

        // Check if image file exists in request
        if (!$request->hasFile('image')) {
            return $this->sendError('No image file found in request', 400);
        }

        // Get authenticated user from Sanctum token
        $authUser = Auth::user();
        $user = User::findOrFail($authUser->id);

        // Get file extension
        $extension = $request->file('image')->getClientOriginalExtension();

        // Generate a unique image name
        $image_name = 'avatar_' . uniqid() . '.' . $extension;

        // Upload to S3 and set public visibility
        $path = $request->file('image')->storeAs(
            'avatars', // PATH_TO_S3_FOLDER_HERE
            $image_name,
            's3'
        );
        Storage::disk('s3')->setVisibility($path, "public");

        // Update user's avatar in database
        $user->avatar = $path;
        $user->save();

        // Prepare success response
        $success['avatar'] = null;
        if (isset($user->avatar)) {
            $success['avatar'] = $this->getS3Url($path);
        }

        return $this->sendResponse($success, 'User profile avatar uploaded successfully!');
    }
    public function removeAvatar()
    {
        // Retrieve the authenticated user using Sanctum
        $authUser = Auth::user();
        $user = User::findOrFail($authUser->id);

        // Delete the file from S3 using the path stored in the user's avatar field
        Storage::disk('s3')->delete($user->avatar);

        // Set the user's avatar field to null and save the updated record
        $user->avatar = null;
        $user->save();

        // Prepare the response with a null avatar value
        $success['avatar'] = null;
        return $this->sendResponse($success, 'User profile avatar removed successfully!');
    }
}