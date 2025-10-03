<?php

namespace App\Console\Commands;

use App\Models\Location;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpsertLocations extends Command
{
    protected $signature = 'upsert:locations';

    protected $description = 'Upserts locations into the database';

    public function handle()
    {
        $locations = [
            [
                'city' => ['en' => 'Amman', 'ar' => 'عمّان'],
                'country' => ['en' => 'Jordan', 'ar' => 'الأردن'],
            ],
            [
                'city' => ['en' => 'Zarqa', 'ar' => 'الزرقاء'],
                'country' => ['en' => 'Jordan', 'ar' => 'الأردن'],
            ],
            [
                'city' => ['en' => 'Irbid', 'ar' => 'إربد'],
                'country' => ['en' => 'Jordan', 'ar' => 'الأردن'],
            ],
            [
                'city' => ['en' => 'Aqaba', 'ar' => 'العقبة'],
                'country' => ['en' => 'Jordan', 'ar' => 'الأردن'],
            ],
            [
                'city' => ['en' => 'Madaba', 'ar' => 'مادبا'],
                'country' => ['en' => 'Jordan', 'ar' => 'الأردن'],
            ],
            [
                'city' => ['en' => 'Karak', 'ar' => 'الكرك'],
                'country' => ['en' => 'Jordan', 'ar' => 'الأردن'],
            ],
            [
                'city' => ['en' => 'Ma\'an', 'ar' => 'معان'],
                'country' => ['en' => 'Jordan', 'ar' => 'الأردن'],
            ],
            [
                'city' => ['en' => 'Jerash', 'ar' => 'جرش'],
                'country' => ['en' => 'Jordan', 'ar' => 'الأردن'],
            ],
            [
                'city' => ['en' => 'Salt', 'ar' => 'السلط'],
                'country' => ['en' => 'Jordan', 'ar' => 'الأردن'],
            ],
            [
                'city' => ['en' => 'Ajloun', 'ar' => 'عجلون'],
                'country' => ['en' => 'Jordan', 'ar' => 'الأردن'],
            ],
        ];

        DB::transaction(function () use ($locations) {
            foreach ($locations as $location) {
                if (Location::where('city->en', $location['city']['en'])->exists()) {
                    $this->info('Location with city "'.$location['city']['en'].'" already exists, skipping');

                    continue;
                }

                Location::firstOrCreate(
                    ['city' => $location['city']],
                    ['country' => $location['country']]
                );
            }
        });

        $this->info('Locations upserted successfully');
    }
}
