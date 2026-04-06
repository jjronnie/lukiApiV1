@props(['appointment'])

<x-popup-modal title="Appointment Details">
    <x-slot name="trigger">
        <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow  p-5 cursor-pointer h-full">
            <div class="flex justify-between items-start mb-4">
                <div class="flex-1">
                    <h3 class="font-semibold text-gray-900 text-lg mb-1">{{ $appointment->service_name }}</h3>
                    <p class="text-sm text-gray-500">{{ $appointment->person_to_see ?? '' }}</p>
                </div>
                <x-status-badge :status="$appointment->status" />
            </div>

            {{-- Appointment date/time --}}
            <div class="flex items-center text-gray-600 text-sm mb-3 font-medium">
                <i class="fa-regular fa-calendar w-5 h-5 mr-3 text-hw-blue"></i>
                {{ $appointment->appointment_date->format('d M, Y \a\t h:i A') }}
            </div>

            {{-- Cancel reason if available --}}
            @if ($appointment->cancel_reason)
                <div class="bg-red-50 border border-red-200 rounded p-3 mt-3">
                    <span class="block font-medium text-red-700 text-sm mb-1">
                        Cancellation Reason:
                    </span>
                    <p class="text-red-900 text-sm break-words">
                        {{ $appointment->cancel_reason }}
                    </p>
                </div>
            @endif

            {{-- Actions --}}
            <div class="flex justify-between items-center mt-4 pt-4 border-t border-gray-200">
                <span class="text-xs text-gray-400">Click to view details</span>
                <div class="flex gap-3" @click.stop>
                    @if ($appointment->status === 'pending')
                        <a href="{{ route('user.appointments.edit', $appointment) }}" title="Edit appointment" class="text-gray-500 hover:text-hw-blue transition-colors">
                            <i class="fa-solid fa-pen-to-square w-5 h-5"></i>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </x-slot>

    {{-- Modal Content --}}
    <div class="space-y-4">
        <div class="flex justify-between items-center border-b border-gray-300 pb-3">
            <span class="text-gray-600 font-medium">Appointment Date:</span>
            <span class="text-black font-semibold">{{ $appointment->appointment_date->format('d M, Y \a\t h:i A') }}</span>
        </div>

        <div class="flex justify-between items-center border-b border-gray-300 pb-3">
            <span class="text-gray-600 font-medium">Status:</span>
            <x-status-badge :status="$appointment->status" />
        </div>

        <div class="border-b border-gray-300 pb-3">
            <span class="block text-gray-600 font-medium mb-2">Service:</span>
            <p class="text-black break-words">{{ $appointment->service_name }}</p>
        </div>

        <div class="border-b border-gray-300 pb-3">
            <span class="block text-gray-600 font-medium mb-2">Person to See:</span>
            <p class="text-black">{{ $appointment->person_to_see ?? 'Not specified' }}</p>
        </div>

        @if ($appointment->notes)
            <div class="border-b border-gray-300 pb-3">
                <span class="block text-gray-600 font-medium mb-2">Notes:</span>
                <p class="text-black break-words">{{ $appointment->notes }}</p>
            </div>
        @endif

        {{-- Confirmed At --}}
        @if ($appointment->confirmed_at)
            <div class="flex justify-between items-center border-b border-gray-300 pb-3">
                <span class="text-gray-600 font-medium">Confirmed At:</span>
                <span class="text-black">{{ $appointment->confirmed_at->format('d M, Y \a\t h:i A') }}</span>
            </div>
        @endif

        {{-- Confirmed By --}}
        @if ($appointment->confirmedBy)
            <div class="flex justify-between items-center border-b border-gray-300 pb-3">
                <span class="text-gray-600 font-medium">Confirmed By:</span>
                <span class="text-black">{{ $appointment->confirmedBy->name }}</span>
            </div>
        @endif

        {{-- Completed At --}}
        @if ($appointment->completed_at)
            <div class="flex justify-between items-center border-b border-gray-300 pb-3">
                <span class="text-gray-600 font-medium">Completed At:</span>
                <span class="text-black">{{ $appointment->completed_at->format('d M, Y \a\t h:i A') }}</span>
            </div>
        @endif

        {{-- Completed By --}}
        @if ($appointment->completedBy)
            <div class="flex justify-between items-center border-b border-gray-300 pb-3">
                <span class="text-gray-600 font-medium">Completed By:</span>
                <span class="text-black">{{ $appointment->completedBy->name }}</span>
            </div>
        @endif

        {{-- Cancelled At --}}
        @if ($appointment->cancelled_at)
            <div class="flex justify-between items-center border-b border-gray-300 pb-3">
                <span class="text-gray-600 font-medium">Cancelled At:</span>
                <span class="text-black">{{ $appointment->cancelled_at->format('d M, Y \a\t h:i A') }}</span>
            </div>
        @endif

        {{-- Cancelled By --}}
        @if ($appointment->cancelledBy)
            <div class="flex justify-between items-center border-b border-gray-300 pb-3">
                <span class="text-gray-600 font-medium">Cancelled By:</span>
                <span class="text-black">{{ $appointment->cancelledBy->name }}</span>
            </div>
        @endif

        {{-- Cancellation Reason --}}
        @if ($appointment->cancel_reason)
            <div class="border-b border-gray-300 pb-3">
                <span class="block text-gray-600 font-medium mb-2">Cancellation Reason:</span>
                <p class="text-black break-words">{{ $appointment->cancel_reason }}</p>
            </div>
        @endif
    </div>
</x-popup-modal>
