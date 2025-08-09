<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\StoreSupportTicketRequest;
use App\Http\Resources\SupportTicketResource;
use App\Models\SupportTicket;
use Illuminate\Support\Facades\Auth;

class SupportTicketController extends ApiController
{
    /**
     * List user support tickets
     * 
     * Retrieve all support tickets for the authenticated user.
     *
     * @group Support Tickets
     * @authenticated
     */
    public function index()
    {
        $this->authorize('viewAny', SupportTicket::class);

        return SupportTicketResource::collection(Auth::user()->supportTickets);
    }

    /**
     * Create a new support ticket
     * 
     * Submit a new support ticket. Can be used by both authenticated and unauthenticated users.
     *
     * @group Support Tickets
     * @unauthenticated
     * 
     * @bodyParam subject string required The subject of the support ticket. Example: Account login issue
     * @bodyParam message string required The detailed message describing the issue. Example: I am unable to log into my account after password reset.
     * @bodyParam email string required The contact email address. Example: user@example.com
     * @bodyParam priority string optional The priority level (low, medium, high). Example: medium
     */
    public function store(StoreSupportTicketRequest $request)
    {
        $ticket = SupportTicket::create($request->mappedAttributes([
            'user_id' => auth('sanctum')->user()?->id,
        ])->toArray());

        return SupportTicketResource::make($ticket);
    }

    /**
     * Get support ticket details
     * 
     * Retrieve detailed information about a specific support ticket.
     * The ticket must belong to the authenticated user.
     *
     * @group Support Tickets
     * @authenticated
     * 
     * @urlParam supportTicket integer required The ID of the support ticket. Example: 1
     */
    public function show(SupportTicket $supportTicket)
    {
        $this->authorize('view', $supportTicket);

        return SupportTicketResource::make($supportTicket);
    }
}
