<?php

namespace App\Http\Controllers\Api\V1\Provider;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Provider\StoreProviderDocumentRequest;
use App\Models\ProviderDocument;
use Illuminate\Http\JsonResponse;

class ProviderDocumentController extends Controller
{
    public function store(StoreProviderDocumentRequest $request): JsonResponse
    {
        $providerProfile = $request->user()->providerProfile()->firstOrFail();

        $data = $request->validated();
        $file = $data['file'];

        $document = ProviderDocument::query()->create([
            'provider_profile_id' => $providerProfile->id,
            'document_type' => $data['document_type'],
            'status' => 'pending',
            'file_hash' => hash_file('sha256', $file->getRealPath()),
        ]);

        $document->addMedia($file)->toMediaCollection('documents');

        return response()->json([
            'message' => 'Document uploaded.',
            'status' => $document->status,
        ], 201);
    }
}
