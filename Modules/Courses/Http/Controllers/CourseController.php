<?php

namespace Modules\Courses\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Core\Traits\ApiResponse;
use Modules\Courses\Models\Course;
use  Modules\Enrollment\Models\Enrollment;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $user = $request->user(); // Sanctum auth
        $courses = Course::where('status', 'active')->get();

        foreach($courses as $course){
            $course['enrolled'] = Enrollment::where(['course_id' => $course->id, 'user_id' =>$user ->id  ])->exists() ? true : false;
        }
        return $this->success($courses);
    }

    public function show($id)
    {
        $course = Course::find($id);
        if (!$course) {
            return $this->error('Course not found', 404);
        }
        $course['enrolled'] = Enrollment::where(['course_id' => $course->id, 'user_id' =>$user ->id ])->exists() ? true : false;
        return $this->success($course);
    }

}