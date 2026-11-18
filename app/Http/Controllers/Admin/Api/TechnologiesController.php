<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Models\Technology;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TechnologiesController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => Technology::query()->orderBy('name')->get(['id', 'name', 'slug']),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:technologies,name'],
        ]);

        $technology = Technology::create([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
        ]);

        return response()->json(['data' => ['id' => $technology->id, 'name' => $technology->name]], 201);
    }

    public function destroy(Technology $technology): JsonResponse
    {
        $technology->delete();

        return response()->json(['data' => ['ok' => true]]);
    }
}
