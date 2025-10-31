<?php

namespace Modules\Partnership\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Modules\Core\Traits\ApiResponse;
use Modules\Partnership\Models\PartnerApplication;
use Modules\Users\Models\User;

class PartnerController extends Controller
{
    use ApiResponse;

    // User applies to become partner
    public function apply()
    {
        $user = request()->user();

        // Check if already applied or is partner
        if (PartnerApplication::where('user_id', $user->id)->exists()) {
            return $this->error('You have already applied', 409);
        }

        if ($user->referral_code) {
            return $this->error('You are already a partner', 409);
        }

        PartnerApplication::create([
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        return $this->success(null, 'Application submitted. We will review shortly.');
    }

    // Admin approves/rejects applications
    public function processApplication(\Modules\Partnership\Http\Requests\ApproveRequest $request)
    {
        $admin = $request->user();
        $application = PartnerApplication::findOrFail($request->application_id);

        if ($request->action === 'approve') {
            // Generate unique referral code
            $referralCode = 'WCTI' . strtoupper(Str::random(6));
            while (User::where('referral_code', $referralCode)->exists()) {
                $referralCode = 'WCTI' . strtoupper(Str::random(6));
            }

            // Update user
            $application->user->update(['referral_code' => $referralCode]);

            // Approve application
            $application->update([
                'status' => 'approved',
                'approved_by' => $admin->id,
            ]);

            return $this->success(null, 'Partner approved. Referral code assigned.');
        }

        if ($request->action === 'reject') {
            $application->update([
                'status' => 'rejected',
                'reason_for_rejection' => $request->reason,
                'approved_by' => $admin->id,
            ]);
            return $this->success(null, 'Application rejected.');
        }
    }

    // Get user's application status
    public function getApplicationStatus()
    {
        $user = request()->user();
        $application = PartnerApplication::where('user_id', $user->id)->first();

        if (!$application) {
            return $this->success(['status' => 'not_applied']);
        }

        return $this->success([
            'status' => $application->status,
            'referral_code' => $user->referral_code,
            'applied_at' => $application->created_at,
        ]);
    }

    // Admin: List all applications
    public function listApplications()
    {
        $applications = PartnerApplication::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return $this->success($applications);
    }
}