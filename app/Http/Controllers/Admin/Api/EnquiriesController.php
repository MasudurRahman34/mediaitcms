<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Models\ContactEnquiry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EnquiriesController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = ContactEnquiry::query()->latest();

        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }

        if ($search = $request->string('q')->trim()->toString()) {
            $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('subject', 'like', "%{$search}%"));
        }

        $enquiries = $query->paginate(20)->withQueryString();

        return response()->json([
            'data' => $enquiries->getCollection()->map(fn (ContactEnquiry $enquiry) => [
                'id' => $enquiry->id,
                'name' => $enquiry->name,
                'email' => $enquiry->email,
                'phone' => $enquiry->phone,
                'company' => $enquiry->company,
                'subject' => $enquiry->subject,
                'message' => $enquiry->message,
                'status' => $enquiry->status,
                'created_at' => $enquiry->created_at,
            ])->values(),
            'meta' => [
                'current_page' => $enquiries->currentPage(),
                'last_page' => $enquiries->lastPage(),
                'total' => $enquiries->total(),
            ],
        ]);
    }

    public function show(ContactEnquiry $enquiry): JsonResponse
    {
        return response()->json(['data' => $enquiry->toArray()]);
    }

    public function update(Request $request, ContactEnquiry $enquiry): JsonResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:new,read,replied,archived'],
        ]);

        $enquiry->update($data);

        return response()->json(['data' => ['ok' => true]]);
    }

    public function destroy(ContactEnquiry $enquiry): JsonResponse
    {
        $enquiry->delete();

        return response()->json(['data' => ['ok' => true]]);
    }
}
