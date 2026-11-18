<?php

namespace App\Http\Middleware;

use App\Models\Redirect;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Serves stored 301/302 redirects (created automatically when a slug changes)
 * after the router has failed to match the incoming path.
 */
class HandleRedirects
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($response->getStatusCode() !== 404) {
            return $response;
        }

        $path = trim($request->path(), '/');

        if ($path === '') {
            return $response;
        }

        $redirect = Redirect::query()->where('from_path', $path)->first();

        if (! $redirect) {
            return $response;
        }

        Redirect::query()->whereKey($redirect->getKey())->increment('hits');

        return redirect()->to(
            str_starts_with($redirect->to_path, 'http') ? $redirect->to_path : url($redirect->to_path),
            $redirect->status_code,
        );
    }
}
