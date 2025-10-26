<?php

namespace Modules\Enrollment\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Core\Helpers\GeneralHelper;
use Modules\Core\Traits\ApiResponse;
use Modules\Enrollment\Http\Requests\EnrollRequest;
use Modules\Enrollment\Models\Enrollment;
    use Modules\Enrollment\Services\AdmissionLetterService;

class EnrollmentController extends Controller
{
    use ApiResponse;

    public function enroll(EnrollRequest $request)
    {
       
        $user = $request->user();
        if (!$user) {
            return $this->error('User not found', 404);
        }

        // Check if already enrolled
        if (Enrollment::where('user_id', $user->id)
            ->where('course_id', $request->course_id)->exists()) {
            return $this->error('Already enrolled in this course', 409);
        }

        // Simulate payment verification (in real app: call payment gateway)
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