<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dispute;
use App\Models\Order;
use App\Models\ProviderProfile;
use App\Models\Service;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $today = now()->toDateString();

        $recentUsers = User::query()
            ->with('roles')
            ->latest()
            ->limit(8)
            ->get();

        $recentOrders = Order::query()
            ->latest()
            ->limit(6)
            ->get(['public_id', 'status', 'total_amount', 'created_at']);

        return view('admin.dashboard', [
            'totalUsers' => User::query()->count(),
            'totalProviders' => ProviderProfile::query()->count(),
            'pendingProviders' => ProviderProfile::query()->where('verification_status', 'pending')->count(),
            'approvedProviders' => ProviderProfile::query()->where('verification_status', 'approved')->count(),
            'activeServices' => Service::query()->where('is_active', true)->count(),
            'todayOrders' => Order::query()->whereDate('created_at', $today)->count(),
            'openDisputes' => Dispute::query()->where('status', 'open')->count(),
            'todayVolume' => (int) Order::query()->whereDate('created_at', $today)->sum('total_amount'),
            'todayCommission' => (int) (WalletTransaction::query()
                ->whereDate('created_at', $today)
                ->where('type', 'commission_deduction')
                ->sum('amount') * -1),
            'recentUsers' => $recentUsers,
            'recentOrders' => $recentOrders,
        ]);
    }
}
