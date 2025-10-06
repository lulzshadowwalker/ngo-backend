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

        // Get the file path - could be in file_path or value field
        $filePath = $response->file_path ?: $response->value;

        // Verify there's a file to download
        if (! $filePath) {
            abort(404, 'No file available for download');
        }

        // Check if file exists in storage
        if (! Storage::disk('public')->exists($filePath)) {
            abort(404, 'File not found');
        }

        // Get the file name for download
        $fileName = basename($filePath);

        // Return the file for download
        return response()->download(
            Storage::disk('public')->path($filePath),
            $fileName
        );
    }
}
