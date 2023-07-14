<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Rating;

class RatingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create dummy data ratings
        $ratings = [
            [
                'value' => 1,
                'title' => 'Sangat kurang kuas',
            ],
            [
                'value' => 2,
                'title' => 'Kurang puas',
            ],
            [
                'value' => 3,
                'title' => 'Netral',
            ],
            [
                'value' => 4,
                'title' => 'Puas',
            ],
            [
                'value' => 5,
                'title' => 'Sangat puas',
            ],
        ];

        // Insert dummy data to database
        foreach ($ratings as $rating) {
            Rating::create($rating);
        }
    }
}
