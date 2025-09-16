<?php

namespace Tests\Feature\Integration;

use App\Enums\ApplicationStatus;
use App\Enums\FormFieldType;
use App\Enums\OpportunityStatus;
use App\Models\Application;
use App\Models\ApplicationForm;
use App\Models\FormField;
use App\Models\Location;
use App\Models\Opportunity;
use App\Models\Organization;
use App\Models\Program;
use App\Models\Sector;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CompleteWorkflowTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $ngoUser;

    private User $individualUser;

    private Organization $organization;

    private Sector $sector;

    private Location $location;

    private Program $program;

    private Opportunity $opportunity;

    private ApplicationForm $applicationForm;

    private FormField $nameField;

    private FormField $emailField;

    private Application $application;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test data
        $this->createTestData();
    }

    /**
     * Test the complete workflow from opportunity creation to application management
     */
    public function test_complete_volunteer_application_workflow()
    {
        // Step 1: Test opportunity discovery API
        $this->testOpportunityDiscoveryEndpoints();

        // Step 2: Test application submission API
        $this->testApplicationSubmissionWorkflow();

        // Step 3: Test application management API
        $this->testApplicationManagementWorkflow();

        // Step 4: Test filtering and search functionality
        $this->testAdvancedFiltering();

        $this->assertTrue(true, 'Complete workflow test passed successfully!');
    }

    private function createTestData(): void
    {
        // Create NGO user (organization admin)
        $this->ngoUser = User::factory()->create([
            'name' => 'NGO Admin',
            'email' => 'ngo@example.com',
        ]);

        // Create individual user (volunteer)
        $this->individualUser = User::factory()->create([
            'name' => 'John Volunteer',
            'email' => 'volunteer@example.com',
        ]);

        // Create sector
        $this->sector = Sector::factory()->create([
            'name' => 'Health & Wellness',
        ]);

        // Create location
        $this->location = Location::factory()->create([
            'city' => 'Amman',
            'country' => 'Jordan',
        ]);

        // Create organization
        $this->organization = Organization::factory()->create([
            'name' => 'Test Health NGO',
            'slug' => 'test-health-ngo',
            'bio' => 'A test NGO focused on health education',
            'website' => 'https://testhealthngo.org',
            'sector_id' => $this->sector->id,
            'location_id' => $this->location->id,
            'contact_email' => 'contact@testhealthngo.org',
        ]);

        // Associate NGO user with organization
        $this->ngoUser->update(['organization_id' => $this->organization->id]);

        // Create program
        $this->program = Program::factory()->create([
            'title' => [
                'en' => 'Community Health Education Program',
                'ar' => 'برنامج التثقيف الصحي المجتمعي',
            ],
            'description' => [
                'en' => 'A comprehensive program to educate communities about health practices',
                'ar' => 'برنامج شامل لتثقيف المجتمعات حول الممارسات الصحية',
            ],
            'organization_id' => $this->organization->id,
        ]);

        // Create opportunity
        $this->opportunity = Opportunity::factory()->create([
            'title' => [
                'en' => 'Community Health Educator',
                'ar' => 'مثقف صحي مجتمعي',
            ],
            'description' => [
                'en' => 'Help educate communities about healthy living practices',
                'ar' => 'ساعد في تثقيف المجتمعات حول ممارسات الحياة الصحية',
            ],
            'organization_id' => $this->organization->id,
            'program_id' => $this->program->id,
            'sector_id' => $this->sector->id,
            'location_id' => $this->location->id,
            'status' => OpportunityStatus::Active,
            'is_featured' => true,
            'duration' => 40, // hours
            'expiry_date' => now()->addMonths(3),
            'tags' => [
                'en' => ['health education', 'community outreach', 'teaching'],
                'ar' => ['تعليم صحي', 'توعية مجتمعية', 'تدريس'],
            ],
            'required_skills' => [
                'en' => ['communication skills', 'health knowledge'],
                'ar' => ['مهارات التواصل', 'المعرفة الصحية'],
            ],
            'latitude' => '31.95000000',
            'longitude' => '35.93330000',
            'location_description' => [
                'en' => 'Community centers in Amman',
                'ar' => 'المراكز المجتمعية في عمان',
            ],
        ]);

        // Create application form
        $this->applicationForm = ApplicationForm::factory()->create([
            'opportunity_id' => $this->opportunity->id,
            'organization_id' => $this->organization->id,
            'title' => [
                'en' => 'Health Educator Application Form',
                'ar' => 'نموذج طلب مثقف صحي',
            ],
            'description' => [
                'en' => 'Please complete this form to apply for the health educator position',
                'ar' => 'يرجى إكمال هذا النموذج للتقدم لمنصب المثقف الصحي',
            ],
            'is_active' => true,
        ]);

        // Create form fields
        $this->nameField = FormField::factory()->create([
            'application_form_id' => $this->applicationForm->id,
            'type' => FormFieldType::Text,
            'label' => [
                'en' => 'Full Name',
                'ar' => 'الاسم الكامل',
            ],
            'placeholder' => [
                'en' => 'Enter your full name',
                'ar' => 'أدخل اسمك الكامل',
            ],
            'is_required' => true,
            'sort_order' => 1,
        ]);

        $this->emailField = FormField::factory()->create([
            'application_form_id' => $this->applicationForm->id,
            'type' => FormFieldType::Email,
            'label' => [
                'en' => 'Email Address',
                'ar' => 'عنوان البريد الإلكتروني',
            ],
            'placeholder' => [
                'en' => 'Enter your email address',
                'ar' => 'أدخل عنوان بريدك الإلكتروني',
            ],
            'is_required' => true,
            'sort_order' => 2,
        ]);
    }

    private function test_opportunity_discovery_endpoints(): void
    {
        // Test 1: Get all opportunities
        $response = $this->getJson('/api/v1/opportunities');
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'description',
                        'status',
                        'isFeatured',
                        'organizationId',
                        'programId',
                        'tags',
                        'duration',
                        'expiryDate',
                        'organization' => ['id', 'name'],
                        'program' => ['id', 'title'],
                        'sector' => ['id', 'name'],
                    ],
                ],
                'meta' => ['total', 'perPage', 'currentPage'],
            ]);

        $this->assertGreaterThan(0, $response->json('meta.total'));

        // Test 2: Get featured opportunities
        $response = $this->getJson('/api/v1/opportunities/featured');
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'title', 'isFeatured'],
                ],
            ]);

        $featuredData = $response->json('data');
        $this->assertNotEmpty($featuredData);
        $this->assertTrue($featuredData[0]['isFeatured']);

        // Test 3: Get opportunity stats
        $response = $this->getJson('/api/v1/opportunities/stats');
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'total_opportunities',
                    'total_organizations',
                    'opportunities_this_month',
                ],
            ]);

        // Test 4: Get specific opportunity with form details
        // First check if form fields were created
        $this->assertGreaterThan(0, $this->applicationForm->formFields()->count(), 'Form fields should be created');

        $response = $this->getJson("/api/v1/opportunities/{$this->opportunity->id}");
        $response->assertStatus(200);

        // Debug the response structure
        $data = $response->json('data');
        $this->assertArrayHasKey('applicationForm', $data, 'Application form should exist in response');
        $this->assertArrayHasKey('formFields', $data['applicationForm'], 'Form fields should exist in application form');

        $response->assertJsonStructure([
            'data' => [
                'id',
                'title',
                'description',
                'organization' => [
                    'id',
                    'name',
                    'bio',
                ],
                'applicationForm' => [
                    'id',
                    'title',
                    'formFields' => [
                        '*' => [
                            'id',
                            'type',
                            'label',
                            'isRequired',
                        ],
                    ],
                ],
            ],
        ]);

        $opportunityData = $response->json('data');
        $this->assertEquals($this->opportunity->id, $opportunityData['id']);
        $this->assertNotEmpty($opportunityData['applicationForm']['formFields']);
    }

    private function test_application_submission_workflow(): void
    {
        // Test 1: Submit application
        $applicationData = [
            'opportunity_id' => $this->opportunity->id,
            'user_id' => $this->individualUser->id, // In real app, this would come from auth
            'responses' => [
                [
                    'form_field_id' => $this->nameField->id,
                    'value' => 'John Volunteer Applicant',
                ],
                [
                    'form_field_id' => $this->emailField->id,
                    'value' => 'john.applicant@example.com',
                ],
            ],
        ];

        $response = $this->postJson('/api/v1/applications', $applicationData);
        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'opportunityId',
                    'userId',
                    'status',
                    'submittedAt',
                    'responses' => [
                        '*' => ['formFieldId', 'value'],
                    ],
                ],
            ]);

        $this->application = Application::find($response->json('data.id'));
        $this->assertNotNull($this->application);
        $this->assertEquals(ApplicationStatus::Pending, $this->application->status);

        // Test 2: Verify application was created with correct responses
        $this->assertEquals(2, $this->application->responses()->count());

        $nameResponse = $this->application->responses()
            ->where('form_field_id', $this->nameField->id)
            ->first();
        $this->assertEquals('John Volunteer Applicant', $nameResponse->value);

        // Test 3: Try to submit duplicate application (should fail)
        $response = $this->postJson('/api/v1/applications', $applicationData);
        $response->assertStatus(400)
            ->assertJson([
                'message' => 'You have already submitted an application for this opportunity',
            ]);
    }

    private function test_application_management_workflow(): void
    {
        // Test 1: List user's applications
        $response = $this->getJson("/api/v1/applications?user_id={$this->individualUser->id}");
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'opportunityId',
                        'status',
                        'submittedAt',
                        'opportunity' => ['id', 'title'],
                        'organization' => ['id', 'name'],
                    ],
                ],
                'meta' => ['total'],
            ]);

        $this->assertEquals(1, $response->json('meta.total'));

        // Test 2: View specific application
        $response = $this->getJson("/api/v1/applications/{$this->application->id}?user_id={$this->individualUser->id}");
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'status',
                    'responses' => [
                        '*' => [
                            'formFieldId',
                            'value',
                            'formField' => ['type', 'label'],
                        ],
                    ],
                ],
            ]);

        // Test 3: Update application (while still pending)
        $updatedData = [
            'user_id' => $this->individualUser->id,
            'responses' => [
                [
                    'form_field_id' => $this->nameField->id,
                    'value' => 'John Updated Volunteer',
                ],
                [
                    'form_field_id' => $this->emailField->id,
                    'value' => 'john.updated@example.com',
                ],
            ],
        ];

        $response = $this->patchJson("/api/v1/applications/{$this->application->id}", $updatedData);
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Application updated successfully',
            ]);

        // Verify the update
        $this->application->refresh();
        $updatedNameResponse = $this->application->responses()
            ->where('form_field_id', $this->nameField->id)
            ->first();
        $this->assertEquals('John Updated Volunteer', $updatedNameResponse->value);

        // Test 4: Try to update after approval (should fail)
        $this->application->update(['status' => ApplicationStatus::Approved]);

        $response = $this->patchJson("/api/v1/applications/{$this->application->id}", $updatedData);
        $response->assertStatus(400)
            ->assertJson([
                'message' => 'Cannot update application that has already been reviewed',
            ]);
    }

    private function test_advanced_filtering(): void
    {
        // Test 1: Filter by tags
        $response = $this->getJson('/api/v1/opportunities?tags=health education');
        $response->assertStatus(200);
        $this->assertGreaterThan(0, $response->json('meta.total'));

        // Test 2: Filter by duration
        $response = $this->getJson('/api/v1/opportunities?max_duration=50');
        $response->assertStatus(200);
        $this->assertGreaterThan(0, $response->json('meta.total'));

        // Test 3: Filter by location coordinates (skip for SQLite as it doesn't support spatial functions)
        if (config('database.default') !== 'sqlite') {
            $response = $this->getJson('/api/v1/opportunities?latitude=31.95&longitude=35.93&radius=10');
            $response->assertStatus(200);
            $this->assertGreaterThan(0, $response->json('meta.total'));
        }

        // Test 4: Search in title/description (using exact title since search uses JSON_CONTAINS)
        $response = $this->getJson('/api/v1/opportunities?search=Community Health Educator');
        $response->assertStatus(200);
        $this->assertGreaterThan(0, $response->json('meta.total'));

        // Test 5: Filter by organization
        $response = $this->getJson("/api/v1/opportunities?organization_id={$this->organization->id}");
        $response->assertStatus(200);
        $this->assertGreaterThan(0, $response->json('meta.total'));

        // Test 6: Combine multiple filters
        $response = $this->getJson("/api/v1/opportunities?tags=health education&max_duration=50&organization_id={$this->organization->id}");
        $response->assertStatus(200);
        $this->assertGreaterThan(0, $response->json('meta.total'));
    }

    /**
     * Test error handling scenarios
     */
    public function test_error_handling_scenarios()
    {
        // Test 1: Submit application for non-existent opportunity
        $response = $this->postJson('/api/v1/applications', [
            'opportunity_id' => 99999,
            'user_id' => $this->individualUser->id,
            'responses' => [],
        ]);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['opportunity_id']);

        // Test 2: Submit application with invalid form field
        $response = $this->postJson('/api/v1/applications', [
            'opportunity_id' => $this->opportunity->id,
            'user_id' => $this->individualUser->id,
            'responses' => [
                [
                    'form_field_id' => 99999,
                    'value' => 'test',
                ],
            ],
        ]);
        $response->assertStatus(422);

        // Test 3: View non-existent opportunity
        $response = $this->getJson('/api/v1/opportunities/99999');
        $response->assertStatus(404);

        // Test 4: View non-existent application
        $response = $this->getJson('/api/v1/applications/99999?user_id=1');
        $response->assertStatus(404);
    }

    // /**
    //  * Test pagination and performance
    //  *
    //  * @test
    //  */
    // public function test_pagination_and_performance()
    // {
    //     // Create additional opportunities for pagination testing
    //     Opportunity::factory(15)->create([
    //         'organization_id' => $this->organization->id,
    //         'program_id' => $this->program->id,
    //         'sector_id' => $this->sector->id,
    //         'status' => OpportunityStatus::Active,
    //         'expiry_date' => now()->addMonths(2),
    //     ]);

    //     // Test pagination
    //     $response = $this->getJson('/api/v1/opportunities?per_page=5');
    //     $response->assertStatus(200);

    //     $data = $response->json();
    //     $this->assertEquals(5, count($data['data']));
    //     $this->assertGreaterThan(1, $data['meta']['lastPage']);

    //     // Test page 2
    //     $response = $this->getJson('/api/v1/opportunities?per_page=5&page=2');
    //     $response->assertStatus(200);

    //     $data = $response->json();
    //     $this->assertEquals(2, $data['meta']['currentPage']);
    // }
}
