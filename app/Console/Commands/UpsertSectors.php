<?php

namespace App\Console\Commands;

use App\Models\Sector;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpsertSectors extends Command
{
    protected $signature = 'upsert:sectors';

    protected $description = 'Upserts sectors into the database';

    public function handle()
    {
        $sectors = [
            [
                'name' => ['en' => 'Anti-Corruption', 'ar' => 'مكافحة الفساد'],
            ],
            [
                'name' => ['en' => 'Human Rights', 'ar' => 'حقوق الإنسان'],
            ],
            [
                'name' => ['en' => 'Education', 'ar' => 'التعليم'],
            ],
            [
                'name' => ['en' => 'Health', 'ar' => 'الصحة'],
            ],
            [
                'name' => ['en' => 'Environment', 'ar' => 'البيئة'],
            ],
            [
                'name' => ['en' => 'Social Services', 'ar' => 'الخدمات الاجتماعية'],
            ],
            [
                'name' => ['en' => 'Women & Gender', 'ar' => 'المرأة والجندر'],
            ],
            [
                'name' => ['en' => 'Governance & Policy', 'ar' => 'الحوكمة والسياسات'],
            ],
            [
                'name' => ['en' => 'Legal Aid & Advocacy', 'ar' => 'المساعدة القانونية والدفاع'],
            ],
            [
                'name' => ['en' => 'Arts & Culture', 'ar' => 'الفنون والثقافة'],
            ],
            [
                'name' => ['en' => 'Technology & Innovation', 'ar' => 'التكنولوجيا والابتكار'],
            ],
            [
                'name' => ['en' => 'Agriculture & Food Security', 'ar' => 'الزراعة والأمن الغذائي'],
            ],
        ];

        DB::transaction(function () use ($sectors) {
            foreach ($sectors as $sector) {
                if (Sector::where('name->en', $sector['name']['en'])->exists()) {
                    $this->info('Sector with name "'.$sector['name']['en'].'" already exists, skipping');

                    continue;
                }

                Sector::firstOrCreate(
                    ['name' => $sector['name']]
                );
            }
        });

        $this->info('Sectors upserted successfully');
    }
}
