<?php

namespace Modules\Enrollment\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Core\Helpers\GeneralHelper;
use Modules\Core\Traits\ApiResponse;
use Modules\Enrollment\Http\Requests\EnrollRequest;
use Modules\Enrollment\Models\Enrollment;
use Modules\Enrollment\Services\AdmissionLetterService;

// 🔴 MISSING IMPORTS (ADD THESE)
use Modules\Users\Models\User; // 👈 Required for User::find()
use Modules\Partnership\Models\PartnerApplication; // 👈 Required for partner check
use Modules\Referral\Models\Referral; // 👈 Required for Referral::create()

class EnrollmentController extends Controller
{
    use ApiResponse;

    public function enroll(EnrollRequest $request)
    {
        $user = $request->user();
        if (!$user) {
            return $this->error('User not found', 404);
        }

        // ✅ CREATE ENROLLMENT FIRST (before referral)
        if (Enrollment::where('user_id', $user->id)
            ->where('course_id', $request->course_id)->exists()) {
            return $this->error('Already enrolled in this course', 409);
        }

        if (strlen($request->payment_reference) < 5) {
            return $this->error('Invalid payment reference', 400);
        }

        $enrollment = Enrollment::create([
            'user_id' => $user->id,
            'course_id' => $request->course_id,
            'matric_number' => GeneralHelper::generateMatricNumber(),
            'payment_status' => 'completed',
            'payment_reference' => $request->payment_reference,
            'status' => 'active',
        ]);

        // ✅ CREATE REFERRAL AFTER ENROLLMENT (SLA: commission on enrollment)
        if ($user->referrer_id) {
            $referrer = User::find($user->referrer_id);
            
            if ($referrer) {
                // Check if referrer is an approved partner
                $isPartner = PartnerApplication::where('user_id', $referrer->id)
                    ->where('status', 'approved')
                    ->exists();

                $commissionAmount = $isPartner ? 200.00 : 100.00;

                Referral::create([
                    'referrer_id' => $referrer->id,
                    'referred_id' => $user->id,
                    'course_id' => $request->course_id,
                    'commission_amount' => $commissionAmount,
                    'status' => 'pending',
                ]);
            }
        }

        return $this->success($enrollment, 'Enrollment successful! Admission letter ready.');
    }

    public function getMatricNumber(Request $request)
    {
        $user = $request->user();
        $enrollment = Enrollment::where('user_id', $user->id)->first();

        if (!$enrollment) {
            return $this->error('No enrollment found', 404);
        }

        return $this->success([
            'matric_number' => $enrollment->matric_number,
            'courses' => $enrollment->course->title,
            'class_starts_at' => now()->addWeek()->toDateString(),
        ]);
    }

    public function getAdmissionLetterUrl(Request $request)
    {
        $user = $request->user();
        $enrollment = Enrollment::where('user_id', $user->id)->first();

        if (!$enrollment) {
            return $this->error('No enrollment found', 404);
        }

        $pdfUrl = AdmissionLetterService::generateAndStoreForEnrollment($enrollment);

        return $this->success([
            'admission_letter_url' => $pdfUrl,
            'matric_number' => $enrollment->matric_number,
        ], 'Admission letter ready');
    }
}