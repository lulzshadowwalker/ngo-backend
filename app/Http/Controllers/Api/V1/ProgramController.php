<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\ProgramResource;
use App\Models\Program;
use Illuminate\Http\Request;

class ProgramController extends Controller
{
    /**
     * List Programs
     *
     * List all programs.
     *
     * @group Programs
     * @unauthenticated
     */
    public function index()
    {
        return ProgramResource::collection(Program::all());
    }

    /**
     * Get Program
     *
     * Get a specific program by its ID.
     *
     * @group Programs
     * @unauthenticated
     *
     * @urlParam program integer required The ID of the program. Example: 1
     */
    public function show(Program $program)
    {
        return ProgramResource::make($program);
    }

    /**
     * Search Programs
     *
     * Search for programs.
     *
     * @group Programs
     * @unauthenticated
     *
     * @queryParam query string The search term. Example: "education"
     */
    public function search(Request $request)
    {
        $query = $request->input('query', '') ?? '';

        $programs = Program::search($query)->get();

        return ProgramResource::collection($programs);
    }
}
