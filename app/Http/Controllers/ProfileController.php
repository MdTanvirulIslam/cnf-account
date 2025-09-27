<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.update_profile', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): JsonResponse
    {
        $user = $request->user();

        try {
            // Fill validated fields except profile_picture
            $user->fill($request->safe()->except(['profile_picture']));

            if ($user->isDirty('email')) {
                $user->email_verified_at = null;
            }

            // Handle profile picture upload
            if ($request->hasFile('profile_picture')) {
                $file = $request->file('profile_picture');

                // Validate file
                if (!$file->isValid()) {
                    throw new \Exception('File upload failed');
                }

                $path = $file->store('avatars', 'public');

                // Verify file was stored
                if (!Storage::disk('public')->exists($path)) {
                    throw new \Exception('File storage failed');
                }

                // Delete old picture if exists
                if ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture)) {
                    Storage::disk('public')->delete($user->profile_picture);
                }

                $user->profile_picture = $path;
            }

            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully.',
                'user' => $user->only(['name', 'email', 'address', 'phone', 'profile_picture'])
            ], 200);

        } catch (\Exception $e) {
            logger('Profile update error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Profile update failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
