<?php

namespace Modules\Core\Enums;

enum UserRole: string
{
    case STUDENT = 'student';
    case MENTOR = 'mentor';
    case PARTNER = 'partner';
    case ADMIN = 'admin';
}