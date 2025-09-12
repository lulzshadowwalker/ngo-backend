<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProgramResource;
use App\Models\Program;
use Illuminate\Http\Request;

class ProgramController extends Controller
{
    public function index()
    {
        return ProgramResource::collection(Program::all());
    }

    public function show(Program $program)
    {
        return ProgramResource::make($program);
    }

    public function search(Request $request)
    {
        $query = $request->input('query', '') ?? '';

        $programs = Program::search($query)->get();

        return ProgramResource::collection($programs);
    }
}
