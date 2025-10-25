<?php

namespace Modules\Core\Helpers;

class GeneralHelper
{
    public static function generateMatricNumber($year = null)
    {
        $year = $year ?? date('Y');
        $lastId = \DB::table('enrollments')->max('id') ?? 0;
        $number = str_pad($lastId + 1, 4, '0', STR_PAD_LEFT);
        return "WCTI/{$year}/{$number}";
    }

    public static function validateNuban($accountNumber, $bankCode)
    {
        // Use a NUBAN validation package or API (e.g., monnify, paystack)
        // For now, return true in dev
        return true;
    }
}