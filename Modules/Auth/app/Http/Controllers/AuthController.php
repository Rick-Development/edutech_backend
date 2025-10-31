<?php

namespace Modules\Auth\app\Http\Controllers;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;
use Modules\Auth\app\Http\Requests\RegisterRequest;
use Modules\Auth\Models\BiometricToken;
use Modules\Core\Traits\ApiResponse;
use Modules\Users\Models\User;
use Modules\Wallet\Services\FlutterwaveService;
use Illuminate\Support\Str;
use Modules\Referral\Models\Referral;


class AuthController extends Controller
{
    use ApiResponse;

    // 1. USER REGISTRATION
    public function register(RegisterRequest $request)
    {
        $referrer = null;
        if ($request->filled('referral_code')) {
            $referrer = User::where('referral_code', $request->referral_code)->first();
        }
        $user = User::create([
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'name' => $request->firstname . ' ' . $request->lastname,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => $request->password,
            'role' => User::ROLE_STUDENT, // Default role
            'referral_code' => 'WCTI' . strtoupper(Str::random(6)),
            'referrer_id' => $referrer?->id, // 👈 STORE REFERRER
            
        ]);

        if($referrer){
            Referral::create([
                'referrer_id' => $referrer->id,
                'referred_id' => $user->id,
                'commission_amount' => 500, // Initial commission
                'status' => 'pending',
            ]);
        }
        // Create Sanctum token for immediate login
        $token = $user->createToken('auth_token')->plainTextToken;
        
            $flutterwave = new FlutterwaveService();
            $response = $flutterwave->createCustomer([
               'email' => $user->email,
            ]);
            $user->customer_id = $response['data']['id'];
            $user->save();
        return $this->success([
            'access_token' => $token,
        ], 'Registration successful. Welcome to Wavecrest!');
    }

    // 2. EMAIL/PASSWORD LOGIN
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'nullable|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return $this->error('Invalid credentials', 401);
        }

        if($user->customer_id == null) {
            $flutterwave = new FlutterwaveService();
            $response = $flutterwave->createCustomer([
               'email' => $user->email,
            ]);
            $user->customer_id = $response['data']['id'];
            $user->save();
        }

        if(!$user->referral_code) {
            // Generate a unique referral code
            
            // Generate code: WCTI + last 6 of UUID or random string
            $referralCode = 'WCTI' . strtoupper(Str::random(6));

            // Ensure uniqueness
            while (User::where('referral_code', $referralCode)->exists()) {
                $referralCode = 'WCTI' . strtoupper(Str::random(6));
            }

            $user->update(['referral_code' => $referralCode]);
        }


        // Revoke old tokens (optional: keep last 3)
        $user->tokens()->delete();

        $token = $user->createToken($request->device_name ?? 'mobile')->plainTextToken;

        return $this->success([
            'access_token' => $token
        ], 'Login successful');
    }

    // 3. ENABLE BIOMETRIC LOGIN (after first successful login)
    public function enableBiometric(Request $request)
    {
        $user = $request->user(); // Sanctum auth

        // Generate biometric token
        $bioToken = BiometricToken::generateForUser($user->id, $request->device_name ?? 'mobile');

        $user->update(['is_biometric_enabled' => true]);

        return $this->success([
            'biometric_token' => $bioToken->token,
        ], 'Biometric login enabled');
    }

    public function disableBiometric(Request $request)
    {
        $user = $request->user(); // Sanctum auth

        // Revoke biometric token
       BiometricToken::where('user_id', $user->id)->delete();

        $user->update(['is_biometric_enabled' => false]);

        return $this->success([
            'message' => 'Biometric login disabled',
        ], 'Biometric login disabled');
    }

    // 4. BIOMETRIC LOGIN (mobile-only)
    public function biometricLogin(Request $request)
    {
        $request->validate([
            'biometric_token' => 'required|string',
        ]);

        $token = BiometricToken::where('token', $request->biometric_token)->first();

        if (!$token) {
            return $this->error('Invalid biometric token', 401);
        }

        $user = $token->user;
        $sanctumToken = $user->createToken('biometric_session')->plainTextToken;

        return $this->success([
            // 'user' => $user,
            'access_token' => $sanctumToken,
            // 'token_type' => 'Bearer',
        ], 'Biometric login successful');
    }

    // 5. LOGOUT
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return $this->success([], 'Logged out successfully');
    }
}