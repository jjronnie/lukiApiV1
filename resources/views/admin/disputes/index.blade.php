<x-app-layout>
    <x-admin.page-header title="Disputes" subtitle="Review and resolve customer disputes">
    </x-admin.page-header>

    <div class="space-y-4">
        @forelse($disputes as $dispute)
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div class="space-y-2">
                        <div class="flex items-center gap-3">
                            <span class="font-mono text-xs text-gray-400">#{{ $dispute->id }}</span>
                            <span class="inline-flex rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-medium text-blue-700">{{ $dispute->status->value ?? $dispute->status }}</span>
                        </div>
                        <div class="grid grid-cols-2 gap-x-6 gap-y-1 text-sm sm:grid-cols-4">
                            <div>
                                <span class="text-xs text-gray-500">Order</span>
                                <p class="font-medium text-gray-900">{{ $dispute->order->public_id }}</p>
                            </div>
                            <div>
                                <span class="text-xs text-gray-500">User</span>
                                <p class="text-gray-700">{{ $dispute->user->email }}</p>
                            </div>
                            <div>
                                <span class="text-xs text-gray-500">Category</span>
                                <p class="text-gray-700">{{ $dispute->category->value ?? $dispute->category }}</p>
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('admin.disputes.resolve', $dispute) }}" class="flex w-full flex-col gap-2 sm:w-auto sm:min-w-[320px]">
                        @csrf
                        <div class="flex gap-2">
                            <select name="status" required class="flex-1 rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-primary/50 focus:ring-2 focus:ring-primary/20">
                                <option value="resolved">Resolved</option>
                                <option value="rejected">Rejected</option>
                            </select>
                            <input name="wallet_adjustment_amount" type="number" placeholder="Wallet adj" class="w-28 rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-primary/50 focus:ring-2 focus:ring-primary/20">
                        </div>
                        <textarea name="resolution_notes" placeholder="Resolution notes" required class="rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-primary/50 focus:ring-2 focus:ring-primary/20" rows="2"></textarea>
                        <button type="submit" class="btn text-xs self-end">Submit Resolution</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="rounded-xl border border-gray-200 bg-white p-12 text-center text-gray-500 shadow-sm">
                No disputes found.
            </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $disputes->links() }}
    </div>
</x-app-layout>
