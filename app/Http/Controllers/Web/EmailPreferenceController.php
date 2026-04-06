<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\UpdateEmailPreferenceRequest;
use App\Models\User;
use App\Services\UserEmailPreferenceService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class EmailPreferenceController extends Controller
{
    public function __construct(
        private readonly UserEmailPreferenceService $preferenceService,
    ) {}

    public function show(Request $request, User $user)
    {
        if (! $request->hasValidSignature()) {
            return $this->invalidResponse($request);
        }

        $preference = $this->preferenceService->ensureForUser($user);

        return view('email-preferences.form', [
            'user' => $user,
            'preference' => $preference,
            'submitUrl' => $request->fullUrl(),
            'expiresAt' => $this->expiresAt($request),
        ]);
    }

    public function update(
        UpdateEmailPreferenceRequest $request,
        User $user,
    ) {
        if (! $request->hasValidSignature()) {
            return $this->invalidResponse($request);
        }

        $preference = $this->preferenceService->ensureForUser($user);
        $data = $request->validated();

        $preference->update([
            'marketing_emails_enabled' => (bool) ($data['marketing_emails_enabled'] ?? false),
            'booking_emails_enabled' => (bool) ($data['booking_emails_enabled'] ?? false),
            'authentication_emails_enabled' => true,
        ]);

        return view('email-preferences.result', [
            'title' => 'Preferences updated',
            'message' => 'Your email preferences have been updated successfully.',
            'tone' => 'success',
        ]);
    }

    private function invalidResponse(Request $request)
    {
        $statusCode = $this->isExpired($request) ? 410 : 403;

        return response()->view('email-preferences.invalid', [
            'title' => $this->isExpired($request) ? 'Link expired' : 'Invalid link',
            'message' => $this->isExpired($request)
                ? 'This email preferences link expired. Please use a newer email link.'
                : 'This email preferences link is invalid or has been changed.',
        ], $statusCode);
    }

    private function isExpired(Request $request): bool
    {
        $expiresAt = (int) $request->query('expires', 0);

        return $expiresAt > 0 && now()->timestamp > $expiresAt;
    }

    private function expiresAt(Request $request): ?Carbon
    {
        $expiresAt = (int) $request->query('expires', 0);

        return $expiresAt > 0 ? now()->setTimestamp($expiresAt) : null;
    }
}
