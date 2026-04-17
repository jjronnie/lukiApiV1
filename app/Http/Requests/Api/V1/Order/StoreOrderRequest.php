<?php

namespace App\Http\Requests\Api\V1\Order;

use App\Enums\PaymentMethod;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $userDefaultPaymentMethod = $this->user()?->default_payment_method?->value
            ?? $this->user()?->default_payment_method;
        $paymentMethods = array_map(static fn (PaymentMethod $method): string => $method->value, PaymentMethod::cases());

        return [
            'service_public_id' => ['required', 'string', 'exists:services,public_id'],
            'service_tier_public_id' => ['required', 'string', 'exists:service_tiers,public_id'],
            'add_on_public_ids' => ['nullable', 'array'],
            'add_on_public_ids.*' => ['string', 'exists:service_add_ons,public_id'],
            'is_scheduled' => ['required', 'boolean'],
            'scheduled_at' => ['nullable', 'date', 'after:now'],
            'address_text' => ['required', 'string', 'max:255'],
            'location_lat' => ['required', 'numeric', 'between:-90,90'],
            'location_lng' => ['required', 'numeric', 'between:-180,180'],
            'place_id' => ['nullable', 'string', 'max:120'],
            'payment_method' => [
                'nullable',
                Rule::requiredIf(blank($userDefaultPaymentMethod)),
                'string',
                Rule::in($paymentMethods),
            ],
            'set_as_default_payment_method' => ['nullable', 'boolean'],
            'distance_km' => ['nullable', 'numeric', 'min:0'],
            'service_minutes' => ['nullable', 'integer', 'min:1'],
            'promo_code' => ['nullable', 'string', 'max:40'],
            'booking_mode' => ['required', 'string', 'in:normal,pair'],
            'pair_provider_number' => ['nullable', 'required_if:booking_mode,pair', 'digits:5'],
            'idempotency_key' => ['nullable', 'string', 'max:120'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $isScheduled = $this->boolean('is_scheduled');
            $bookingMode = (string) $this->input('booking_mode', 'normal');

            if ($bookingMode === 'pair' && $isScheduled) {
                $validator->errors()->add('booking_mode', 'Pair bookings are only available for immediate requests.');
            }

            if (! $isScheduled) {
                return;
            }

            if (! $this->filled('scheduled_at')) {
                $validator->errors()->add('scheduled_at', 'Select a date and time for a scheduled booking.');

                return;
            }

            try {
                $scheduledAt = Carbon::parse((string) $this->input('scheduled_at'))->setTimezone('Africa/Kampala');
            } catch (\Throwable) {
                $validator->errors()->add('scheduled_at', 'Selected schedule is invalid.');

                return;
            }

            $minutes = ((int) $scheduledAt->format('H') * 60) + (int) $scheduledAt->format('i');
            if ($minutes < 7 * 60 || $minutes > 18 * 60) {
                $validator->errors()->add('scheduled_at', 'Scheduled bookings must be between 7:00 AM and 6:00 PM.');
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'payment_method.required' => 'Choose a payment method before continuing.',
        ];
    }
}
