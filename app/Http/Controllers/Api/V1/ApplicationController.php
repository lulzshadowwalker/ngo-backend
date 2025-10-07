<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ApplicationStatus;
use App\Enums\FormFieldType;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\ApplicationResource;
use App\Models\Application;
use App\Models\ApplicationResponse;
use App\Models\Opportunity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ApplicationController extends Controller
{
    /**
     * Display a listing of the user's applications
     *
     * @group Applications
     *
     * @authenticated
     *
     * @queryParam user_id integer The ID of the user to retrieve applications for. Will be replaced by authenticated user.
     * @queryParam per_page integer Number of items per page. Defaults to 10. Example: 15
     */
    public function index(Request $request): JsonResponse
    {
        $userId = Auth::id();

        if (! $userId) {
            return response()->json([
                'message' => 'User ID is required',
            ], 400);
        }

        $applications = Application::with([
            'opportunity:id,title,description,expiry_date',
            'organization:id,name,bio',
            'applicationForm:id,title,description',
        ])
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 10));

        return response()->json([
            'data' => ApplicationResource::collection($applications->items()),
            'meta' => [
                'total' => $applications->total(),
                'perPage' => $applications->perPage(),
                'currentPage' => $applications->currentPage(),
                'lastPage' => $applications->lastPage(),
                'from' => $applications->firstItem(),
                'to' => $applications->lastItem(),
            ],
        ]);
    }

    /**
     * Store a newly created application
     *
     * @group Applications
     *
     * @authenticated
     *
     * @bodyParam opportunity_id integer required The ID of the opportunity being applied for. Example: 1
     * @bodyParam responses array required An array of responses to the application form fields.
     * @bodyParam responses.*.form_field_id integer required The ID of the form field. Example: 1
     * @bodyParam responses.*.value mixed The user's response to the field (for non-file fields). Example: "I have 5 years of experience."
     * @bodyParam file_{field_id} file For file upload fields, use file_{field_id} as the key. Example: file_6
     */
    public function store(Request $request): JsonResponse
    {
        // First validate basic structure
        $validator = Validator::make($request->all(), [
            'opportunity_id' => 'required|exists:opportunities,id',
            'responses' => 'required|array',
            'responses.*.form_field_id' => 'required|exists:form_fields,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $opportunity = Opportunity::with('applicationForm.formFields')->findOrFail($request->opportunity_id);

        // Check if opportunity is still active and not expired
        if ($opportunity->status !== \App\Enums\OpportunityStatus::Active || $opportunity->expiry_date < now()) {
            return response()->json([
                'message' => 'This opportunity is no longer available for applications',
            ], 400);
        }

        // Check if user already has an application for this opportunity
        $existingApplication = Application::where('user_id', Auth::id())
            ->where('opportunity_id', $request->opportunity_id)
            ->first();

        if ($existingApplication) {
            return response()->json([
                'message' => 'You have already submitted an application for this opportunity',
            ], 400);
        }

        // Validate responses based on field types
        $formFields = $opportunity->applicationForm->formFields->keyBy('id');
        $validationRules = [];
        $validationMessages = [];

        foreach ($request->responses as $index => $responseData) {
            $formField = $formFields->get($responseData['form_field_id']);

            if (! $formField) {
                return response()->json([
                    'message' => 'Invalid form field ID: '.$responseData['form_field_id'],
                ], 422);
            }

            $fieldKey = "responses.{$index}";

            // Validate based on field type
            if ($formField->type === FormFieldType::File) {
                $fileKey = "file_{$formField->id}";
                if ($formField->is_required) {
                    $validationRules[$fileKey] = 'required|file|max:10240'; // 10MB max
                    $validationMessages["{$fileKey}.required"] = "File is required for field: {$formField->label}";
                } else {
                    $validationRules[$fileKey] = 'nullable|file|max:10240'; // 10MB max
                }
                $validationMessages["{$fileKey}.max"] = "File size cannot exceed 10MB for field: {$formField->label}";
            } else {
                $validationRules["{$fieldKey}.value"] = $formField->is_required ? 'required' : 'nullable';
                if ($formField->is_required) {
                    $validationMessages["{$fieldKey}.value.required"] = "This field is required: {$formField->label}";
                }
            }
        }

        $responseValidator = Validator::make($request->all(), $validationRules, $validationMessages);

        if ($responseValidator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $responseValidator->errors(),
            ], 422);
        }

        return DB::transaction(function () use ($request, $opportunity, $formFields) {
            // Create the application
            $application = Application::create([
                'application_form_id' => $opportunity->applicationForm->id,
                'user_id' => Auth::id(),
                'opportunity_id' => $request->opportunity_id,
                'organization_id' => $opportunity->organization_id,
                'status' => ApplicationStatus::Pending,
                'submitted_at' => now(),
            ]);

            // Process responses with file handling
            foreach ($request->responses as $responseData) {
                $formField = $formFields->get($responseData['form_field_id']);
                $value = null;
                $filePath = null;

                if ($formField->type === FormFieldType::File) {
                    // Handle file upload - look for file with form field ID as key
                    $fileKey = "file_{$formField->id}";
                    if ($request->hasFile($fileKey)) {
                        $file = $request->file($fileKey);

                        // Generate unique filename
                        $fileName = time().'_'.$formField->id.'_'.$file->getClientOriginalName();

                        // Store file in public disk under applications directory
                        $filePath = $file->storeAs(
                            "applications/{$application->id}",
                            $fileName,
                            'public'
                        );

                        // Store original filename in value for reference
                        $value = $file->getClientOriginalName();
                    }
                } else {
                    // Handle regular field values
                    $value = $responseData['value'] ?? null;
                }

                ApplicationResponse::create([
                    'application_id' => $application->id,
                    'form_field_id' => $responseData['form_field_id'],
                    'value' => $value,
                    'file_path' => $filePath,
                ]);
            }

            $application->load(['opportunity', 'organization', 'applicationForm', 'responses.formField']);

            return response()->json([
                'message' => 'Application submitted successfully',
                'data' => new ApplicationResource($application),
            ], 201);
        });
    }

    /**
     * Display the specified application
     *
     * @group Applications
     *
     * @authenticated
     *
     * @urlParam id string required The ID of the application. Example: "1"
     *
     * @queryParam user_id integer The ID of the user (for validation, will be replaced by authenticated user).
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $userId = Auth::id();

        $application = Application::with([
            'opportunity:id,title,description,expiry_date',
            'organization:id,name,bio',
            'applicationForm:id,title,description',
            'responses.formField',
        ])
            ->where('id', $id)
            ->when($userId, function ($query, $userId) {
                return $query->where('user_id', $userId);
            })
            ->firstOrFail();

        return response()->json([
            'data' => new ApplicationResource($application),
        ]);
    }

    /**
     * Update the specified application (only for draft applications)
     *
     * @group Applications
     *
     * @authenticated
     *
     * @urlParam id string required The ID of the application to update. Example: "1"
     *
     * @bodyParam responses array required An array of new responses.
     * @bodyParam responses.*.form_field_id integer required The ID of the form field. Example: 1
     * @bodyParam responses.*.value mixed The user's new response (for non-file fields). Example: "My updated answer."
     * @bodyParam file_{field_id} file For file upload fields, use file_{field_id} as the key. Example: file_6
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $application = Application::with('applicationForm.formFields')->findOrFail($id);

        if ($application->user_id != Auth::id()) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        // Only allow updates to pending applications
        if ($application->status !== ApplicationStatus::Pending) {
            return response()->json([
                'message' => 'Cannot update application that has already been reviewed',
            ], 400);
        }

        // Basic validation
        $validator = Validator::make($request->all(), [
            'responses' => 'sometimes|array',
            'responses.*.form_field_id' => 'required|exists:form_fields,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        return DB::transaction(function () use ($request, $application) {
            if ($request->has('responses')) {
                // Get form fields for validation
                $formFields = $application->applicationForm->formFields->keyBy('id');

                // Delete old files before deleting responses
                $oldResponses = $application->responses()->with('formField')->get();
                foreach ($oldResponses as $oldResponse) {
                    if ($oldResponse->file_path && Storage::disk('public')->exists($oldResponse->file_path)) {
                        Storage::disk('public')->delete($oldResponse->file_path);
                    }
                }

                // Delete existing responses
                $application->responses()->delete();

                // Create new responses with file handling
                foreach ($request->responses as $responseData) {
                    $formField = $formFields->get($responseData['form_field_id']);
                    $value = null;
                    $filePath = null;

                    if ($formField->type === FormFieldType::File) {
                        // Handle file upload - look for file with form field ID as key
                        $fileKey = "file_{$formField->id}";
                        if ($request->hasFile($fileKey)) {
                            $file = $request->file($fileKey);

                            // Generate unique filename
                            $fileName = time().'_'.$formField->id.'_'.$file->getClientOriginalName();

                            // Store file in public disk under applications directory
                            $filePath = $file->storeAs(
                                "applications/{$application->id}",
                                $fileName,
                                'public'
                            );

                            // Store original filename in value for reference
                            $value = $file->getClientOriginalName();
                        }
                    } else {
                        // Handle regular field values
                        $value = $responseData['value'] ?? null;
                    }

                    ApplicationResponse::create([
                        'application_id' => $application->id,
                        'form_field_id' => $responseData['form_field_id'],
                        'value' => $value,
                        'file_path' => $filePath,
                    ]);
                }
            }

            $application->touch(); // Update the updated_at timestamp

            $application->load(['opportunity', 'organization', 'applicationForm', 'responses.formField']);

            return response()->json([
                'message' => 'Application updated successfully',
                'data' => new ApplicationResource($application),
            ]);
        });
    }

    /**
     * Remove the specified application (only for pending applications)
     *
     * @group Applications
     *
     * @authenticated
     *
     * @urlParam id string required The ID of the application to delete. Example: "1"
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $application = Application::findOrFail($id);

        if ($application->user_id != Auth::id()) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        // Only allow deletion of pending applications
        if ($application->status !== ApplicationStatus::Pending) {
            return response()->json([
                'message' => 'Cannot delete application that has already been reviewed',
            ], 400);
        }

        return DB::transaction(function () use ($application) {
            // Delete uploaded files first
            $responses = $application->responses()->get();
            foreach ($responses as $response) {
                if ($response->file_path && Storage::disk('public')->exists($response->file_path)) {
                    Storage::disk('public')->delete($response->file_path);
                }
            }

            // Delete responses first due to foreign key constraints
            $application->responses()->delete();

            // Delete the application directory if it exists and is empty
            $applicationDir = "applications/{$application->id}";
            if (Storage::disk('public')->exists($applicationDir)) {
                $files = Storage::disk('public')->files($applicationDir);
                if (empty($files)) {
                    Storage::disk('public')->deleteDirectory($applicationDir);
                }
            }

            $application->delete();

            return response()->json([
                'message' => 'Application deleted successfully',
            ]);
        });
    }
}
