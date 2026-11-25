<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Storage;

class AdminProfileController extends Controller
{
    /**
     * Display the authenticated administrator's profile.
     */
    public function index(Request $request)
    {
        return view('admin.profile', [
            'admin' => $request->user(),
        ]);
    }

    /**
     * Update the administrator's personal information.
     */

    public function update(Request $request)
    {
        $admin = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($admin->id),
            ],
            'profile_photo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
        ]);

        // Remove the uploaded file from the normal text fields.
        unset($validated['profile_photo']);

        $oldPhoto = $admin->profile_photo_path;
        $newPhoto = null;

        // Store the new photo if one was selected.
        if ($request->hasFile('profile_photo')) {
            $newPhoto = $request->file('profile_photo')
                ->store('profile-photos', 'public');
        }

        // Update name and email.
        $admin->fill($validated);

        // Update photo only if a new one was uploaded.
        if ($newPhoto) {
            $admin->profile_photo_path = $newPhoto;
        }

        $admin->save();

        // Delete the previous photo after the new data is saved.
        if ($newPhoto && $oldPhoto) {
            Storage::disk('public')->delete($oldPhoto);
        }

        return redirect()
            ->route('admin.profile')
            ->with('success', 'Profile updated successfully.');
    }


    /**
     * Update the administrator's password.
     */
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => [
                'required',
                'current_password',
            ],
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(8),
            ],
        ]);

        $admin = $request->user();

        // The User model automatically hashes the password.
        $admin->password = $validated['password'];
        $admin->save();

        return redirect()
            ->route('admin.profile')
            ->with('success', 'Password changed successfully.');
    }

    /**
     * Upload or replace the administrator's profile photo.
     */
    public function updatePhoto(Request $request)
    {
        $request->validate([
            'profile_photo' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
        ]);

        $admin = $request->user();

        // Store the new photo first.
        $newPath = $request->file('profile_photo')
            ->store('profile-photos', 'public');

        // Delete the previous photo after successful upload.
        if ($admin->profile_photo_path) {
            Storage::disk('public')->delete($admin->profile_photo_path);
        }

        $admin->profile_photo_path = $newPath;
        $admin->save();

        return redirect()
            ->route('admin.profile')
            ->with('success', 'Profile photo updated successfully.');
    }

    /**
     * Remove the administrator's profile photo.
     */
    public function removePhoto(Request $request)
    {
        $admin = $request->user();

        if ($admin->profile_photo_path) {
            Storage::disk('public')->delete($admin->profile_photo_path);

            $admin->profile_photo_path = null;
            $admin->save();
        }

        return redirect()
            ->route('admin.profile')
            ->with('success', 'Profile photo removed successfully.');
    }
}
