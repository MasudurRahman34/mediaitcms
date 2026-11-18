<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminShellController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user()->loadMissing('role', 'avatar');

        return view('admin.app', [
            'user' => $user,
            'permissions' => $user->permissionSlugs()->values(),
        ]);
    }
}
