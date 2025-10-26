<?php

namespace Modules\Enrollment\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Modules\Enrollment\Models\Enrollment;

class AdmissionLetterService
{
    public static function generateAndStoreForEnrollment(Enrollment $enrollment)
    {
        $data = [
            'student_name' => $enrollment->user->name,
            'matric_number' => $enrollment->matric_number,
            'course_title' => $enrollment->course->title,
            'class_start_date' => now()->addWeek()->format('F j, Y'),
            'issue_date' => now()->format('F j, Y'),
        ];

        $pdf = Pdf::loadView('enrollment::letter', $data)
            ->setPaper('a4');

        // Sanitize filename
        $safeMatric = preg_replace('/[^a-zA-Z0-9._-]/', '_', $enrollment->matric_number);
        $filename = "admission_letters/{$safeMatric}.pdf";

        // Save to storage/app/public
        Storage::disk('public')->put($filename, $pdf->output());

        // Return public URL
        return Storage::disk('public')->url($filename);
    }
}