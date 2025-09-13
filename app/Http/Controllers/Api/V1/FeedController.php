<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\SearchFeedRequest;
use App\Http\Resources\V1\OpportunityResource;
use App\Http\Resources\V1\OrganizationResource;
use App\Http\Resources\V1\ProgramResource;
use App\Models\Organization;
use App\Models\Program;
use App\Models\Opportunity;

class FeedController extends Controller
{
    public function search(SearchFeedRequest $request)
    {
        $query = $request->input('query', '');
        $type = $request->input('type');

        $searchTypes = $type ? [$type] : ['organization', 'program', 'opportunity'];

        $results = [
            'organizations' => [],
            'programs' => [],
            'opportunities' => [],
        ];

        foreach ($searchTypes as $searchType) {
            switch ($searchType) {
                case 'organization':
                    $results['organizations'] = OrganizationResource::collection(Organization::search($query)->get());
                    break;
                case 'program':
                    $results['programs'] = ProgramResource::collection(Program::search($query)->get());
                    break;
                case 'opportunity':
                    $results['opportunities'] = OpportunityResource::collection(Opportunity::search($query)->get());
                    break;
            }
        }

        return response()->json($results);
    }
}
