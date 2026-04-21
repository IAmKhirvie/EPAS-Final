<?php

namespace App\Http\Controllers;

use App\Constants\Roles;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Http\Requests\UpdateProfileRequest;
use App\Models\User;
use App\Models\Setting;
use App\Services\PHPMailerService;

class SettingsController extends Controller
{
    /**
     * Hours to wait before email can be changed again.
     * Configured via config/joms.php → auth.email_change_cooldown_hours
     */

    /**
     * Display the settings page
     */
    public function index()
    {
        $user = Auth::user();
        $settings = $this->getUserSettings($user);
        $systemSettings = $this->getSystemSettings();

        return view('settings.index', compact('user', 'settings', 'systemSettings'));
    }

    /**
     * Resend email verification
     */
    public function resendVerification(Request $request)
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->back()->with('success', 'Your email is already verified.');
        }

        try {
            $mailer = app(PHPMailerService::class);

            $verificationUrl = URL::temporarySignedRoute(
                'verification.verify',
                now()->addMinutes(60),
                [
                    'id' => $user->getKey(),
                    'hash' => sha1($user->getEmailForVerification()),
                ]
            );

            $result = $mailer->sendVerificationEmail($user, $verificationUrl);

            if ($result) {
                Log::info("Verification email resent to: {$user->email}");
                return redirect()->back()->with('success', 'Verification email sent! Please check your inbox.');
            } else {
                Log::error("Failed to resend verification email to: {$user->email}");
                return redirect()->back()->withErrors(['email' => 'Failed to send verification email. Please try again.']);
            }
        } catch (\Exception $e) {
            Log::error("Exception resending verification email: " . $e->getMessage());
            return redirect()->back()->withErrors(['email' => 'An error occurred. Please try again.']);
        }
    }

    /**
     * Update profile settings
     */
    public function updateProfile(UpdateProfileRequest $request)
    {
        $user = Auth::user();

        $validated = $request->validated();

        $oldEmail = $user->email;
        $newEmail = $request->email;
        $emailChanged = $oldEmail !== $newEmail;

        // Check cooldown if email is being changed
        if ($emailChanged && $user->email_changed_at) {
            $cooldownHours = config('joms.auth.email_change_cooldown_hours', 24);
            $hoursSinceLastChange = now()->diffInHours($user->email_changed_at);
            $hoursRemaining = $cooldownHours - $hoursSinceLastChange;

            if ($hoursRemaining > 0) {
                return redirect()->back()
                    ->withErrors(['email' => "You can only change your email once every {$cooldownHours} hours. Please wait {$hoursRemaining} more hour(s)."])
                    ->withInput();
            }
        }

        // Update user data (without email first if it changed)
        $user->update($request->only(['first_name', 'middle_name', 'last_name', 'phone', 'bio']));

        // If email changed, require re-verification
        if ($emailChanged) {
            $user->update([
                'email' => $newEmail,
                'email_verified_at' => null,
                'email_changed_at' => now(),
            ]);

            // Send verification email to new address
            try {
                $mailer = app(PHPMailerService::class);
                $verificationUrl = URL::temporarySignedRoute(
                    'verification.verify',
                    now()->addMinutes(60),
                    [
                        'id' => $user->getKey(),
                        'hash' => sha1($user->getEmailForVerification()),
                    ]
                );

                $result = $mailer->sendVerificationEmail($user, $verificationUrl);

                if ($result) {
                    Log::info("Verification email sent to user ID: {$user->id}");
                    return redirect()->back()->with('success', 'Profile updated! Please verify your new email address. Check your inbox for the verification link.');
                } else {
                    Log::warning("Failed to send verification email for user ID: {$user->id}");
                    return redirect()->back()->with('success', 'Profile updated!')->with('warning', 'Could not send verification email. Please check SMTP settings or use the resend option.');
                }
            } catch (\Exception $e) {
                Log::error("Failed to send verification email", ['user_id' => $user->id, 'error' => $e->getMessage()]);
                return redirect()->back()->with('success', 'Profile updated!')->with('warning', 'Could not send verification email. Please use the resend option.');
            }
        }

        return redirect()->back()->with('success', 'Profile updated successfully!');
    }

    /**
     * Update profile picture
     */
    public function updateProfilePicture(Request $request)
    {
        $user = Auth::user();

        // Handle cropped image (base64)
        if ($request->filled('cropped_image')) {
            try {
                $croppedData = $request->input('cropped_image');

                // Validate base64 format
                if (!preg_match('/^data:image\/(jpeg|png|gif);base64,/', $croppedData, $matches)) {
                    return redirect()->back()->withErrors(['cropped_image' => 'Invalid image format.']);
                }

                $extension = $matches[1] === 'jpeg' ? 'jpg' : $matches[1];

                // Decode base64
                $imageData = base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $croppedData));

                // Validate size (2MB max for base64 decoded)
                if (strlen($imageData) > 2 * 1024 * 1024) {
                    return redirect()->back()->withErrors(['cropped_image' => 'Image size must be less than 2MB.']);
                }

                // Delete old image if exists
                if ($user->profile_image) {
                    Storage::disk('public')->delete('profile-images/' . $user->profile_image);
                }

                // Store new cropped image
                $imageName = Str::uuid() . '.' . $extension;
                Storage::disk('public')->put('profile-images/' . $imageName, $imageData);

                $user->update(['profile_image' => $imageName]);

                return redirect()->back()->with('success', 'Profile picture updated successfully!');
            } catch (\Exception $e) {
                Log::error('Failed to process cropped image', ['error' => $e->getMessage(), 'user_id' => $user->id]);
                return redirect()->back()->withErrors(['cropped_image' => 'Failed to process image. Please try again.']);
            }
        }

        // Handle regular file upload (fallback)
        $request->validate([
            'profile_image' => 'required|image|mimes:jpeg,png,jpg,gif|mimetypes:image/jpeg,image/png,image/gif|max:' . config('joms.uploads.max_image_size', 5120),
        ], [
            'profile_image.required' => 'Please select an image to upload.',
            'profile_image.image' => 'The file must be a valid image.',
            'profile_image.mimes' => 'Only JPG, PNG, and GIF files are allowed.',
            'profile_image.mimetypes' => 'The file content does not match an allowed image type (JPEG, PNG, GIF).',
            'profile_image.max' => 'Image size must be less than ' . (config('joms.uploads.max_image_size', 5120) / 1024) . 'MB.',
        ]);

        // Delete old image if exists
        if ($user->profile_image) {
            Storage::disk('public')->delete('profile-images/' . $user->profile_image);
        }

        // Store new image
        $imageName = Str::uuid() . '.' . $request->profile_image->extension();
        $request->profile_image->storeAs('profile-images', $imageName, 'public');

        $user->update(['profile_image' => $imageName]);

        return redirect()->back()->with('success', 'Profile picture updated successfully!');
    }

    /**
     * Update password
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => [
                'required',
                'min:8',
                'confirmed',
                'regex:' . config('joms.password.regex'),
            ],
        ], [
            'password.regex' => config('joms.password.message', 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.'),
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return redirect()->back()->withErrors(['current_password' => 'Current password is incorrect']);
        }

        try {
            $user->update(['password' => Hash::make($request->password)]);

            return redirect()->back()->with('success', 'Password changed successfully!');
        } catch (\Exception $e) {
            Log::error('Password update failed', ['error' => $e->getMessage(), 'user_id' => Auth::id()]);
            return redirect()->back()->with('error', 'Failed to update password. Please try again.');
        }
    }

    /**
     * Update notification preferences
     */
    public function updateNotifications(Request $request)
    {
        $user = Auth::user();

        $notifications = [
            'email_announcements' => $request->has('email_announcements'),
            'email_grades' => $request->has('email_grades'),
            'email_grade_posted' => $request->has('email_grade_posted'),
            'email_reminders' => $request->has('email_reminders'),
            'email_deadline_reminder' => $request->has('email_deadline_reminder'),
            'push_enabled' => $request->has('push_enabled'),
        ];

        $user->update(['notification_preferences' => $notifications]);

        return redirect()->back()->with('success', 'Notification preferences updated!');
    }

    /**
     * Update appearance settings
     */
    public function updateAppearance(Request $request)
    {
        $user = Auth::user();

        $theme = $request->input('theme', 'light');
        $appearance = [
            'theme' => $theme,
            'sidebar_compact' => $request->has('sidebar_compact'),
            'font_size' => $request->input('font_size', 'medium'),
        ];

        $this->saveUserSetting($user, 'appearance', json_encode($appearance));

        // Sync theme to cookie so layout JS can read it before page renders
        $cookie = cookie('theme', $theme === 'auto' ? '' : $theme, 60 * 24 * 365);

        return redirect()->back()->with('success', 'Appearance settings updated!')->withCookie($cookie);
    }

    /**
     * Admin: Update system settings
     */
    public function updateSystem(Request $request)
    {
        if (Auth::user()->role !== Roles::ADMIN) {
            abort(403);
        }

        $request->validate([
            'site_name' => 'required|string|max:255',
            'registration_enabled' => 'sometimes|boolean',
            'require_approval' => 'sometimes|boolean',
            'passing_score' => 'required|integer|min:50|max:100',
        ]);

        $this->saveSystemSetting('site_name', $request->site_name);
        $this->saveSystemSetting('registration_enabled', $request->has('registration_enabled') ? '1' : '0');
        $this->saveSystemSetting('require_approval', $request->has('require_approval') ? '1' : '0');
        $this->saveSystemSetting('passing_score', $request->passing_score);

        return redirect()->back()->with('success', 'System settings updated!');
    }

    /**
     * Export user data (GDPR compliance)
     */
    public function exportData()
    {
        $user = Auth::user();

        $data = [
            'profile' => $user->toArray(),
            'settings' => $this->getUserSettings($user),
            'exported_at' => now()->toIso8601String(),
        ];

        return response()->json($data)
            ->header('Content-Disposition', 'attachment; filename="my-data.json"');
    }

    /**
     * Delete account
     */
    public function deleteAccount(Request $request)
    {
        $request->validate([
            'confirmation' => 'required|in:DELETE',
            'password' => 'required',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->password, $user->password)) {
            return redirect()->back()->withErrors(['password' => 'Password is incorrect']);
        }

        try {
            // Deactivate and anonymize the account
            $user->forceFill([
                'email' => 'deleted_' . $user->id . '@deleted.local',
                'first_name' => 'Deleted',
                'last_name' => 'User',
                'stat' => 0,
                'password' => Hash::make(Str::random(64)),
                'remember_token' => null,
                'reset_token' => null,
                'two_factor_secret' => null,
                'two_factor_backup_codes' => null,
            ])->save();

            // Invalidate session and log out
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/')->with('success', 'Your account has been deleted.');
        } catch (\Exception $e) {
            Log::error('Account deletion failed', ['error' => $e->getMessage(), 'user_id' => $user->id]);
            return redirect()->back()->with('error', 'Failed to delete account. Please try again.');
        }
    }

    // Helper methods
    private function getUserSettings($user)
    {
        $notificationDefaults = [
            'email_announcements' => true,
            'email_grades' => true,
            'email_reminders' => true,
            'push_enabled' => false,
        ];

        $appearanceDefaults = [
            'theme' => 'light',
            'sidebar_compact' => false,
            'font_size' => 'medium',
        ];

        // Read notifications from User model (where NotificationService reads them)
        $notificationPrefs = $user->notification_preferences ?? [];
        $notifications = array_merge($notificationDefaults, array_intersect_key($notificationPrefs, $notificationDefaults));

        // Read appearance from settings table
        $appearance = $appearanceDefaults;
        try {
            $stored = Setting::where('user_id', $user->id)->where('key', 'appearance')->value('value');
            if ($stored) {
                $appearance = array_merge($appearanceDefaults, json_decode($stored, true) ?? []);
            }
        } catch (\Exception $e) {
            Log::warning('Failed to load user settings', ['user_id' => $user->id, 'error' => $e->getMessage()]);
        }

        return [
            'notifications' => $notifications,
            'appearance' => $appearance,
        ];
    }

    private function saveUserSetting($user, $key, $value)
    {
        try {
            Setting::updateOrCreate(
                ['user_id' => $user->id, 'key' => $key],
                ['value' => $value]
            );
        } catch (\Exception $e) {
            Log::warning('Failed to save user setting', ['user_id' => $user->id, 'key' => $key, 'error' => $e->getMessage()]);
        }
    }

    private function getSystemSettings()
    {
        $defaults = [
            'site_name' => 'EPAS-E Learning Management System',
            'registration_enabled' => true,
            'require_approval' => true,
            'passing_score' => 75,
        ];

        try {
            $stored = Setting::whereNull('user_id')->pluck('value', 'key')->toArray();

            foreach ($defaults as $key => $value) {
                if (isset($stored[$key])) {
                    $defaults[$key] = is_bool($value) ? ($stored[$key] === '1') : $stored[$key];
                }
            }
        } catch (\Exception $e) {
            Log::warning('Failed to load system settings', ['error' => $e->getMessage()]);
        }

        return $defaults;
    }

    private function saveSystemSetting($key, $value)
    {
        try {
            Setting::updateOrCreate(
                ['user_id' => null, 'key' => $key],
                ['value' => $value]
            );
        } catch (\Exception $e) {
            Log::warning('Failed to save system setting', ['key' => $key, 'error' => $e->getMessage()]);
        }
    }
}
