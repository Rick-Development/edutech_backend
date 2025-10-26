<?php

namespace Modules\Courses\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Core\Traits\ApiResponse;
use Modules\Courses\Models\Course;

class CourseController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $courses = Course::where('status', 'active')->get();
        return $this->success($courses);
    }

    public function show($id)
    {
        $course = Course::find($id);
        if (!$course) {
            return $this->error('Course not found', 404);
        }
        return $this->success($course);
    }
}