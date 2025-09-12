<?php

namespace App\Console\Commands;

use App\Enums\{ApplicationStatus, FormFieldType, OpportunityStatus, ProgramStatus};
use App\Models\{Application, ApplicationForm, FormField, Opportunity, Organization, Program, Sector, User};
use Illuminate\Console\Command;

class TestDynamicForms extends Command
{
    protected $signature = 'test:dynamic-forms';
    protected $description = 'Test the dynamic forms system end-to-end';

    public function handle()
    {
        $this->info('🚀 Testing Dynamic Forms System...');

        // Get or create test organization
        $org = Organization::first();
        if (!$org) {
            $this->error('No organizations found. Please create one first.');
            return;
        }

        $this->info("Using organization: {$org->name}");

        // Create test program
        $program = Program::create([
            'organization_id' => $org->id,
            'title' => ['en' => 'Community Volunteer Program', 'ar' => 'برنامج التطوع المجتمعي'],
            'description' => ['en' => 'A program for community volunteers'],
            'status' => ProgramStatus::Active,
        ]);

        $this->info("✅ Created program: {$program->getTranslation('title', 'en')}");

        // Create test opportunity
        $sector = Sector::first();
        $opportunity = Opportunity::create([
            'organization_id' => $org->id,
            'program_id' => $program->id,
            'sector_id' => $sector->id,
            'title' => ['en' => 'Food Distribution Volunteer', 'ar' => 'متطوع توزيع الطعام'],
            'description' => ['en' => 'Help distribute food to families in need'],
            'location' => ['en' => 'Community Center'],
            'tags' => ['volunteering', 'community', 'food'],
            'status' => OpportunityStatus::Active,
            'duration_hours' => 4,
            'start_date' => now()->addDays(7),
            'end_date' => now()->addDays(30),
            'max_participants' => 20,
        ]);

        $this->info("✅ Created opportunity: {$opportunity->getTranslation('title', 'en')}");

        // Create application form
        $form = ApplicationForm::create([
            'organization_id' => $org->id,
            'opportunity_id' => $opportunity->id,
            'title' => ['en' => 'Volunteer Application Form', 'ar' => 'نموذج طلب التطوع'],
            'description' => ['en' => 'Please fill out this form to apply'],
            'is_active' => true,
        ]);

        $this->info("✅ Created application form: {$form->getTranslation('title', 'en')}");

        // Create form fields
        $fields = [
            [
                'type' => FormFieldType::Text,
                'label' => ['en' => 'Full Name', 'ar' => 'الاسم الكامل'],
                'placeholder' => ['en' => 'Enter your full name'],
                'is_required' => true,
                'sort_order' => 1,
            ],
            [
                'type' => FormFieldType::Email,
                'label' => ['en' => 'Email Address', 'ar' => 'عنوان البريد الإلكتروني'],
                'placeholder' => ['en' => 'your.email@example.com'],
                'is_required' => true,
                'sort_order' => 2,
            ],
            [
                'type' => FormFieldType::Textarea,
                'label' => ['en' => 'Why do you want to volunteer?', 'ar' => 'لماذا تريد التطوع؟'],
                'placeholder' => ['en' => 'Tell us about your motivation...'],
                'is_required' => true,
                'sort_order' => 3,
            ],
            [
                'type' => FormFieldType::Select,
                'label' => ['en' => 'Experience Level', 'ar' => 'مستوى الخبرة'],
                'options' => ['en' => ['Beginner', 'Intermediate', 'Advanced'], 'ar' => ['مبتدئ', 'متوسط', 'متقدم']],
                'is_required' => true,
                'sort_order' => 4,
            ],
        ];

        foreach ($fields as $fieldData) {
            $field = FormField::create(array_merge($fieldData, [
                'application_form_id' => $form->id,
            ]));
            $this->info("✅ Created field: {$field->getTranslation('label', 'en')} ({$field->type->value})");
        }

        // Test form relationships
        $form->load('formFields', 'opportunity', 'organization');
        $this->info("📊 Form has {$form->formFields->count()} fields");

        // Create a sample user for testing application submission
        $user = User::firstOrCreate([
            'email' => 'volunteer@example.com'
        ], [
            'name' => 'John Volunteer',
            'password' => bcrypt('password'),
        ]);

        // Create sample application
        $application = Application::create([
            'application_form_id' => $form->id,
            'user_id' => $user->id,
            'opportunity_id' => $opportunity->id,
            'organization_id' => $org->id,
            'status' => ApplicationStatus::Pending,
            'submitted_at' => now(),
        ]);

        $this->info("✅ Created application from user: {$user->name}");

        // Create sample responses
        foreach ($form->formFields as $field) {
            $sampleResponse = match ($field->type) {
                FormFieldType::Text => 'John Volunteer',
                FormFieldType::Email => 'volunteer@example.com',
                FormFieldType::Textarea => 'I want to help my community by volunteering.',
                FormFieldType::Select => 'Intermediate',
                default => 'Sample response',
            };

            $application->responses()->create([
                'form_field_id' => $field->id,
                'response_value' => $sampleResponse,
            ]);
        }

        $this->info("✅ Created {$application->responses()->count()} application responses");

        $this->info('🎉 Dynamic Forms System Test Complete!');
        $this->info('You can now access the CMS at: http://127.0.0.1:8000/cms/login');
        $this->info('- Application Forms: Create and manage dynamic forms');
        $this->info('- Applications: Review and process submitted applications');

        return Command::SUCCESS;
    }
}
