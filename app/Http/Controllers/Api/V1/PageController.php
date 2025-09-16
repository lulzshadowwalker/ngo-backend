<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\PageResource;
use App\Models\Page;

class PageController extends Controller
{
    /**
     * List all pages
     *
     * Retrieve a list of all static pages in the system.
     * Pages contain static content like About Us, Terms of Service, etc.
     *
     * @group Pages
     *
     * @unauthenticated
     */
    public function index()
    {
        return PageResource::collection(Page::all());
    }

    /**
     * Get page details
     *
     * Retrieve detailed information about a specific page including its content.
     *
     * @group Pages
     *
     * @unauthenticated
     *
     * @urlParam page integer required The ID of the page. Example: 1
     */
    public function show(Page $page)
    {
        return PageResource::make($page);
    }
}
