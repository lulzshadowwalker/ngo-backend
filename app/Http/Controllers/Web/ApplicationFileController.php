<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\ApplicationResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class ApplicationFileController extends Controller
{
    public function download(Request $request, Application $application, ApplicationResponse $response)
    {
        // Verify the response belongs to the application
        if ($response->application_id !== $application->id) {
            abort(404);
        }

        // Get the file path - prioritize file_path field, fallback to value field for backwards compatibility
        $filePath = $response->file_path ?: $response->value;

        // Verify there's a file to download
        if (! $filePath) {
            abort(404, 'No file available for download');
        }

        // For backwards compatibility, if the value field contains just a filename (no path),
        // try to construct the expected path
        if (! $response->file_path && $response->value && is_string($response->value) && ! str_contains($response->value, '/')) {
            // This might be just a filename in the value field, try to find it in the application directory
            $possiblePath = "applications/{$application->id}/{$response->value}";
            if (Storage::disk('public')->exists($possiblePath)) {
                $filePath = $possiblePath;
            }
        }

        // Check if file exists in storage
        if (! Storage::disk('public')->exists($filePath)) {
            abort(404, 'File not found in storage');
        }

        // Get the file name for download (use original filename from value field if available and is string)
        $fileName = (is_string($response->value) && $response->value) ? $response->value : basename($filePath);

        // Return the file for download
        return response()->download(
            Storage::disk('public')->path($filePath),
            $fileName
        );
    }
}
