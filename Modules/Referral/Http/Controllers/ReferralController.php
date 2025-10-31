<?php

namespace Modules\Referral\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Core\Traits\ApiResponse;
use Modules\Referral\Models\Referral;
use Modules\Enrollment\Models\Enrollment;
use Modules\Users\Models\User;
use Illuminate\Support\Str;

class ReferralController extends Controller
{
    use ApiResponse;

    // Get referral dashboard stats
   public function getDashboard()
{
    $user = request()->user();
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

    $totalReferrals = Referral::where('referrer_id', $user->id)->count();

    $pendingCommissions = Referral::where('referrer_id', $user->id)
        ->where('status', 'pending')
        ->sum('commission_amount');

    $paidCommissions = Referral::where('referrer_id', $user->id)
        ->where('status', 'paid')
        ->sum('commission_amount');

    // ✅ CORRECT: Count referred users with active enrollments
    $activeStudents = Referral::join('enrollments', 'referrals.referred_id', '=', 'enrollments.user_id')
        ->where('referrals.referrer_id', $user->id)
        ->where('enrollments.status', 'active')
        ->count();

    return $this->success([
        'referral_link' => url('/r/' . $user->referral_code),
        'referral_code' => $user->referral_code,
        'total_referrals' => $totalReferrals,
        'pending_commissions' => $pendingCommissions,
        'paid_commissions' => $paidCommissions,
        'active_students' => $activeStudents,
    ]);
}

    // Get referral history
    public function getHistory()
    {
        $user = request()->user();
        $referrals = Referral::with(['referred', 'course'])
            ->where('referrer_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->success($referrals);
    }
}