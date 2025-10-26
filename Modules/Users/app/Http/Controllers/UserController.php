<?php

namespace Modules\Users\app\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Users\app\Http\Requests\UpdateProfileRequest;
use Illuminate\Support\Facades\Auth;
use Modules\Auth\Models\BiometricToken;

class UserController extends Controller
{
    public function profile()
    {
        $user = Auth::user();
        return response()->json([
            'success' => true,
            'data' => $user,
        ]);
    }

    public function updateProfile(UpdateProfileRequest $request)
    {
        $user = Auth::user();
        $data = $request->validated();

        $user->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully.',
            'data' => $user->fresh(),
        ]);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);

        $user = Auth::user();

        if (!\Illuminate\Support\Facades\Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect.',
            ], 400);
        }

        $user->password = $request->new_password;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Password updated successfully.',
        ]);
    }

    public function getBiometricToken()
    {
        $user = Auth::user();
        $token = BiometricToken::where('user_id', $user->id)->first();
        return response()->json([
            'success' => true,
            'data' => [
                'biometric_token' => $token ? $token->token : null,
                'is_biometric_enabled' => $user->is_biometric_enabled,
            ],
        ]);
    }

    public function updateNotificationSettings(Request $request)
    {
        $request->validate([
            'status' => 'required|boolean',
        ]);

        $user = Auth::user();
        $user->email_notifications = $request->status;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Notification settings updated successfully.',
            'data' => [
                'email_notifications' => $user->email_notifications,
            ],
        ]);
    }

    public function updateTwoFactorAuth(Request $request)
    {
        $request->validate([
            'status' => 'required|boolean',
        ]);

        $user = Auth::user();
        $user->two_factor_auth = $request->status;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Two-factor authentication settings updated successfully.',
            'data' => [
                'two_factor_auth' => $user->two_factor_auth,
            ],
        ]);
    }

    public function deleteAccount(Request $request)
    {
        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $user = Auth::user();

        $user->delete_account = true;
        // $user->delete_reason = $request->reason;

        return response()->json([
            'success' => true,
            'message' => 'Account deleted successfully.',
        ]);
    }

}
