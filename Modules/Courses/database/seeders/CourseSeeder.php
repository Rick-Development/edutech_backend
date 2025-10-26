<?php

namespace Modules\Courses\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Courses\Models\Course;

class CourseSeeder extends Seeder
{
    public function run()
    {
        Course::create([
            'title' => 'Forex Trading Course',
            'description' => 'Master the forex market from scratch.',
            'price' => 25000.00,
            'duration_weeks' => 6,
            'is_incoming' => false,
        ]);

        Course::create([
            'title' => 'Crypto Trading Course',
            'description' => 'Learn to trade Bitcoin, Ethereum, and altcoins.',
            'price' => 20000.00,
            'duration_weeks' => 4,
            'is_incoming' => false,
        ]);

        Course::create([
            'title' => 'Stock Market Course',
            'description' => 'Coming soon: Invest in global stock markets.',
            'price' => 30000.00,
            'duration_weeks' => 8,
            'is_incoming' => true,
        ]);

        Course::create([
            'title' => 'Blockchain Technology',
            'description' => 'Coming soon: Understand blockchain and Web3.',
            'price' => 35000.00,
            'duration_weeks' => 6,
            'is_incoming' => true,
        ]);
    }
}