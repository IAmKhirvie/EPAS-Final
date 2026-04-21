<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    public function update(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'student_id' => 'nullable|string|max:20',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg|mimetypes:image/jpeg,image/png|max:' . config('joms.uploads.max_image_size', 5120)
        ]);
        
        try {
            if ($request->has('student_id')) {
                $user->student_id = $request->student_id;
            }

            if ($request->hasFile('avatar')) {
                // Delete old avatar if exists
                if ($user->profile_image) {
                    Storage::disk('public')->delete('profile-images/' . $user->profile_image);
                }

                // Store new avatar
                $avatarPath = $request->file('avatar')->store('profile-images', 'public');
                $user->profile_image = basename($avatarPath);
            }

            $user->save();

            return back()->with('success', 'Profile updated successfully!');
        } catch (\Exception $e) {
            Log::error('Profile update failed', ['error' => $e->getMessage(), 'user_id' => Auth::id()]);
            return back()->with('error', 'Failed to update profile. Please try again.');
        }
    }

    public function updateAvatar(Request $request)
    {
        $user = Auth::user();

        try {
            // Handle cropped image (base64)
            if ($request->filled('cropped_image')) {
                $croppedData = $request->input('cropped_image');

                // Validate base64 format
                if (!preg_match('/^data:image\/(jpeg|png|gif);base64,/', $croppedData, $matches)) {
                    return back()->with('error', 'Invalid image format.');
                }

                $extension = $matches[1] === 'jpeg' ? 'jpg' : $matches[1];

                // Decode base64
                $imageData = base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $croppedData));

                // Validate size (2MB max)
                if (strlen($imageData) > 2 * 1024 * 1024) {
                    return back()->with('error', 'Image size must be less than 2MB.');
                }

                // Delete old avatar if exists
                if ($user->profile_image) {
                    Storage::disk('public')->delete('profile-images/' . $user->profile_image);
                }

                // Store new cropped image
                $imageName = Str::uuid() . '.' . $extension;
                Storage::disk('public')->put('profile-images/' . $imageName, $imageData);

                $user->profile_image = $imageName;
                $user->save();

                return back()->with('success', 'Profile picture updated successfully!');
            }

            // Handle regular file upload (fallback)
            $request->validate([
                'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|mimetypes:image/jpeg,image/png,image/gif|max:' . config('joms.uploads.max_image_size', 5120)
            ]);

            if ($request->hasFile('avatar')) {
                // Delete old avatar if exists
                if ($user->profile_image) {
                    Storage::disk('public')->delete('profile-images/' . $user->profile_image);
                }

                // Store new avatar
                $avatarPath = $request->file('avatar')->store('profile-images', 'public');
                $user->profile_image = basename($avatarPath);
                $user->save();
            }

            return back()->with('success', 'Profile picture updated successfully!');
        } catch (\Exception $e) {
            Log::error('Avatar update failed', ['error' => $e->getMessage(), 'user_id' => Auth::id()]);
            return back()->with('error', 'Failed to update profile picture. Please try again.');
        }
    }
}