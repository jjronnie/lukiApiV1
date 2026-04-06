@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h2 class="font-display text-3xl">Superadmin Dashboard</h2>
            <p class="mt-1 text-sm text-zinc-400">Live operational snapshot for marketplace activity and governance.</p>
        </div>
        <div class="rounded-xl border border-white/10 bg-white/[0.03] px-3 py-2 text-xs text-zinc-300">
            Updated {{ now()->format('H:i') }} EAT
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <article class="admin-card">
            <p class="admin-card-label">Total Users</p>
            <p class="admin-card-value">{{ number_format($totalUsers) }}</p>
            <p class="admin-card-meta">Registered marketplace customers</p>
        </article>
        <article class="admin-card">
            <p class="admin-card-label">Providers</p>
            <p class="admin-card-value">{{ number_format($totalProviders) }}</p>
            <p class="admin-card-meta">Approved {{ number_format($approvedProviders) }} · Pending {{ number_format($pendingProviders) }}</p>
        </article>
        <article class="admin-card">
            <p class="admin-card-label">Orders Today</p>
            <p class="admin-card-value">{{ number_format($todayOrders) }}</p>
            <p class="admin-card-meta">Open disputes {{ number_format($openDisputes) }}</p>
        </article>
        <article class="admin-card">
            <p class="admin-card-label">Today Volume</p>
            <p class="admin-card-value">UGX {{ number_format($todayVolume) }}</p>
            <p class="admin-card-meta">Commission UGX {{ number_format($todayCommission) }}</p>
        </article>
    </div>

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
        <article class="admin-card lg:col-span-2">
            <div class="mb-4 flex items-center justify-between gap-3">
                <h3 class="font-display text-xl">Recent Users</h3>
                <span class="text-xs uppercase tracking-[0.12em] text-zinc-500">Latest {{ $recentUsers->count() }}</span>
            </div>

            <div class="overflow-x-auto rounded-xl border border-white/10">
                <table class="min-w-[720px]">
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Joined</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($recentUsers as $user)
                        <tr>
                            <td class="font-medium text-zinc-100">{{ $user->name }}</td>
                            <td class="text-zinc-300">{{ $user->email }}</td>
                            <td class="text-zinc-200">{{ $user->roles->pluck('name')->first() ?? 'user' }}</td>
                            <td>
                                <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium {{ $user->is_blocked ? 'bg-rose-400/15 text-rose-200' : 'bg-emerald-400/15 text-emerald-200' }}">
                                    {{ $user->is_blocked ? 'blocked' : 'active' }}
                                </span>
                            </td>
                            <td class="text-zinc-400">{{ $user->created_at?->format('d M, Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-zinc-500">No users available yet.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </article>

        <article class="admin-card">
            <div class="mb-4 flex items-center justify-between gap-3">
                <h3 class="font-display text-xl">Recent Orders</h3>
                <span class="text-xs uppercase tracking-[0.12em] text-zinc-500">Live Feed</span>
            </div>

            <div class="space-y-3">
                @forelse($recentOrders as $order)
                    <div class="rounded-xl border border-white/10 bg-white/[0.03] p-3">
                        <div class="flex items-center justify-between gap-3">
                            <p class="truncate text-sm font-medium text-zinc-100">{{ $order->public_id }}</p>
                            <span class="rounded-full bg-cyan-300/15 px-2 py-1 text-[11px] uppercase tracking-[0.08em] text-cyan-100">{{ $order->status->value ?? $order->status }}</span>
                        </div>
                        <div class="mt-2 flex items-center justify-between text-xs text-zinc-400">
                            <span>UGX {{ number_format($order->total_amount) }}</span>
                            <span>{{ $order->created_at?->diffForHumans() }}</span>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-zinc-500">No recent orders.</p>
                @endforelse
            </div>
        </article>
    </div>

    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
        <article class="rounded-xl border border-white/10 bg-white/[0.02] p-4">
            <p class="text-xs uppercase tracking-[0.12em] text-zinc-500">Services</p>
            <p class="mt-2 text-2xl font-display">{{ number_format($activeServices) }}</p>
        </article>
        <article class="rounded-xl border border-white/10 bg-white/[0.02] p-4">
            <p class="text-xs uppercase tracking-[0.12em] text-zinc-500">Disputes Open</p>
            <p class="mt-2 text-2xl font-display">{{ number_format($openDisputes) }}</p>
        </article>
        <article class="rounded-xl border border-white/10 bg-white/[0.02] p-4">
            <p class="text-xs uppercase tracking-[0.12em] text-zinc-500">Pending Providers</p>
            <p class="mt-2 text-2xl font-display">{{ number_format($pendingProviders) }}</p>
        </article>
        <article class="rounded-xl border border-white/10 bg-white/[0.02] p-4">
            <p class="text-xs uppercase tracking-[0.12em] text-zinc-500">Commission Today</p>
            <p class="mt-2 text-2xl font-display">{{ number_format($todayCommission) }}</p>
        </article>
    </div>
</div>
@endsection
