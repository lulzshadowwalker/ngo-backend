<?php

namespace App\Console\Commands;

use App\Models\Skill;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpsertSkills extends Command
{
    protected $signature = 'upsert:skills';

    protected $description = 'Upserts skills into the database';

    public function handle()
    {
        $skills = [
            [
                'name' => ['en' => 'Communication', 'ar' => 'التواصل'],
            ],
            [
                'name' => ['en' => 'Leadership', 'ar' => 'القيادة'],
            ],
            [
                'name' => ['en' => 'Project Management', 'ar' => 'إدارة المشاريع'],
            ],
            [
                'name' => ['en' => 'Data Analysis', 'ar' => 'تحليل البيانات'],
            ],
            [
                'name' => ['en' => 'Public Speaking', 'ar' => 'الكلام العام'],
            ],
            [
                'name' => ['en' => 'Teamwork', 'ar' => 'العمل الجماعي'],
            ],
            [
                'name' => ['en' => 'Problem Solving', 'ar' => 'حل المشكلات'],
            ],
            [
                'name' => ['en' => 'Creativity', 'ar' => 'الإبداع'],
            ],
            [
                'name' => ['en' => 'Time Management', 'ar' => 'إدارة الوقت'],
            ],
            [
                'name' => ['en' => 'Adaptability', 'ar' => 'التكيف'],
            ],
        ];

        DB::transaction(function () use ($skills) {
            foreach ($skills as $skill) {
                if (Skill::where('name->en', $skill['name']['en'])->exists()) {
                    $this->info('Skill with name "'.$skill['name']['en'].'" already exists, skipping');

                    continue;
                }

                Skill::firstOrCreate(
                    ['name' => $skill['name']]
                );
            }
        });

        $this->info('Skills upserted successfully');
    }
}
