<?php

namespace App\Filament\Cms\Resources\PostResource\Pages;

use App\Filament\Cms\Resources\PostResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePost extends CreateRecord
{
    protected static string $resource = PostResource::class;
}
