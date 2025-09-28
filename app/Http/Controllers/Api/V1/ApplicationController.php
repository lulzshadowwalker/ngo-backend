<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ApplicationStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\ApplicationResource;
use App\Models\Application;
use App\Models\ApplicationResponse;
use App\Models\Opportunity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
     * @bodyParam user_id integer required The ID of the user applying (will be replaced by authenticated user). Example: 1
     * @bodyParam responses array required An array of responses to the application form fields.
     * @bodyParam responses.*.form_field_id integer required The ID of the form field. Example: 1
     * @bodyParam responses.*.value mixed required The user's response to the field. Example: "I have 5 years of experience."
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'opportunity_id' => 'required|exists:opportunities,id',
            'responses' => 'required|array',
            'responses.*.form_field_id' => 'required|exists:form_fields,id',
            'responses.*.value' => 'required',
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

        return DB::transaction(function () use ($request, $opportunity) {
            // Create the application
            $application = Application::create([
                'application_form_id' => $opportunity->applicationForm->id,
                'user_id' => Auth::id(),
                'opportunity_id' => $request->opportunity_id,
                'organization_id' => $opportunity->organization_id,
                'status' => ApplicationStatus::Pending,
                'submitted_at' => now(),
            ]);

            // Validate and create responses
            $formFields = $opportunity->applicationForm->formFields->keyBy('id');
            foreach ($request->responses as $responseData) {
                $formField = $formFields->get($responseData['form_field_id']);

                if (! $formField) {
                    throw new \Exception('Invalid form field ID: '.$responseData['form_field_id']);
                }

                // TODO: Add more specific validation based on field type
                ApplicationResponse::create([
                    'application_id' => $application->id,
                    'form_field_id' => $responseData['form_field_id'],
                    'value' => $responseData['value'],
                    'file_path' => $responseData['file_path'] ?? null,
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
     * @bodyParam responses.*.value mixed required The user's new response. Example: "My updated answer."
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $application = Application::findOrFail($id);

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

        $validator = Validator::make($request->all(), [
            'responses' => 'sometimes|array',
            'responses.*.form_field_id' => 'required|exists:form_fields,id',
            'responses.*.value' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        return DB::transaction(function () use ($request, $application) {
            if ($request->has('responses')) {
                // Delete existing responses and create new ones
                $application->responses()->delete();

                foreach ($request->responses as $responseData) {
                    ApplicationResponse::create([
                        'application_id' => $application->id,
                        'form_field_id' => $responseData['form_field_id'],
                        'value' => $responseData['value'],
                        'file_path' => $responseData['file_path'] ?? null,
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
            // Delete responses first due to foreign key constraints
            $application->responses()->delete();
            $application->delete();

            return response()->json([
                'message' => 'Application deleted successfully',
            ]);
        });
    }
}
