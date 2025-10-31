<?php

namespace Modules\Partnership\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Modules\Core\Traits\ApiResponse;
use Modules\Partnership\Models\PartnerApplication;
use Modules\Users\Models\User;
use Illuminate\Http\Request;
use Modules\Partnership\Http\Requests\ApproveRequest;
use Modules\Referral\Models\Referral;
use Modules\Enrollment\Models\Enrollment;


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

        // if ($user->referral_code) {
        //     return $this->error('You are already a partner', 409);
        // }

        PartnerApplication::create([
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        return $this->success(null, 'Application submitted. We will review shortly.');
    }

    // Admin approves/rejects applications
    public function processApplication(Request $request)
    {
        $admin = $request->user();
        $application = PartnerApplication::findOrFail($request->application_id);

       if ($request->action === 'approve') {
            $user = $application->user;
            // In PartnerController.php
            $partnershipCode = 'PTN' . date('Ymd') . str_pad($application->id, 4, '0', STR_PAD_LEFT);

            // Mark as approved partner (no new code needed)
            $application->update([
                'status' => 'approved',
                'approved_by' => $admin->id,
                'partnership_code' => $partnershipCode,
            ]);

            // User keeps same referral_code, but now earns ₦200
            return $this->success([
                'referral_code' => $user->referral_code,
                'commission_rate' => 200, // Upgraded rate
            ]);
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



public function getDashboard()
{
    $user = request()->user();

    // 1. Check if user is an approved partner
    $application = PartnerApplication::where('user_id', $user->id)
        ->where('status', 'approved')
        ->first();

    if (!$application) {
        return $this->error('Access denied. You are not an approved partner.', 403);
    }

    // 2. Get referral stats
    $totalReferrals = Referral::where('referrer_id', $user->id)->count();
    
    $pendingCommissions = Referral::where('referrer_id', $user->id)
        ->where('status', 'pending')
        ->sum('commission_amount');

    $paidCommissions = Referral::where('referrer_id', $user->id)
        ->where('status', 'paid')
        ->sum('commission_amount');

    // 3. Get active students (referred users with active enrollments)
    $activeStudents = Referral::join('enrollments', 'referrals.referred_id', '=', 'enrollments.user_id')
        ->where('referrals.referrer_id', $user->id)
        ->where('enrollments.status', 'active')
        ->count();

    // 4. Sub-affiliates (optional: partners referred by this partner)
    // For now, we'll skip sub-affiliates (can add later)

    return $this->success([
        'referral_link' => url('/r/' . $user->referral_code),
        'partnership_code' => $application->partnership_code,
        'total_referrals' => $totalReferrals,
        'active_students' => $activeStudents,
        'pending_commissions' => (float) $pendingCommissions,
        'paid_commissions' => (float) $paidCommissions,
        'can_withdraw' => $pendingCommissions >= 1000, // Min withdrawal threshold
    ]);
}


}