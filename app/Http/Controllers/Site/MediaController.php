<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Serves media that lives on a non-public disk. Public uploads are served
 * directly by the storage symlink and never reach this controller.
 */
class MediaController extends Controller
{
    public function __invoke(Request $request, Media $media): StreamedResponse
    {
        abort_if($media->disk === 'public', 404);

        $user = $request->user();

        abort_unless($user && $user->hasAnyPermission(['media.manage', 'content.manage', 'articles.manage']), 403);

        abort_unless(Storage::disk($media->disk)->exists($media->path), 404);

        return Storage::disk($media->disk)->response($media->path, $media->filename);
    }
}
