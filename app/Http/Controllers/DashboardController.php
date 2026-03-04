<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $user = Auth::user();
        $data = [
            'chartData' => [],
            'stats' => [],
        ];

        $days = collect(range(4, 0))->map(fn($i) => Carbon::today()->subDays($i));

        if ($user->isAdmin()) {
            $totalVolume = Task::where('status', 'completed')
                ->whereMonth('completed_at', Carbon::now()->month)
                ->whereYear('completed_at', Carbon::now()->year)
                ->sum('payout');

            $totalInProgressVolume = Task::where('status', 'in_progress')->sum('payout');
            $commissionPercent = (float) Setting::get('admin_commission_percentage', 0);
            
            $adminRevenue = ($totalVolume * $commissionPercent) / 100;
            $adminProjectedRevenue = ($totalInProgressVolume * $commissionPercent) / 100;

            $data['stats'] = [
                'pending' => Task::where('status', '!=', 'completed')->count(),
                'completed' => Task::where('status', 'completed')->count(),
                'total_projected_revenue' => $adminProjectedRevenue,
                'monthly_revenue' => $adminRevenue,
                'commission_rate' => $commissionPercent,
            ];

            $data['chartData'] = $days->map(function ($date) {
                return [
                    'date' => $date->format('M d'),
                    'created' => Task::whereDate('created_at', $date)->count(),
                    'completed' => Task::where('status', 'completed')
                        ->whereDate('completed_at', $date)
                        ->count(),
                ];
            });
        } elseif ($user->isCompany()) {
            $data['stats'] = [
                'pending' => Task::where('company_id', $user->id)->where('status', '!=', 'completed')->count(),
                'completed' => Task::where('company_id', $user->id)->where('status', 'completed')->count(),
                'monthly_spent' => Task::where('company_id', $user->id)
                    ->where('status', 'completed')
                    ->whereMonth('completed_at', Carbon::now()->month)
                    ->whereYear('completed_at', Carbon::now()->year)
                    ->sum('payout'),
                'total_committed' => Task::where('company_id', $user->id)
                    ->where('status', 'in_progress')
                    ->sum('payout'),
            ];

            $data['chartData'] = $days->map(function ($date) use ($user) {
                return [
                    'date' => $date->format('M d'),
                    'created' => Task::where('company_id', $user->id)
                        ->whereDate('created_at', $date)
                        ->count(),
                    'completed' => Task::where('company_id', $user->id)
                        ->where('status', 'completed')
                        ->whereDate('completed_at', $date)
                        ->count(),
                ];
            });
        } elseif ($user->isProfessional()) {
            $data['stats'] = [
                'pending' => $user->assignedTasks()->where('status', '!=', 'completed')->count(),
                'completed' => $user->assignedTasks()->where('status', 'completed')->count(),
                'monthly_earnings' => $user->assignedTasks()->where('status', 'completed')
                    ->whereMonth('completed_at', Carbon::now()->month)
                    ->whereYear('completed_at', Carbon::now()->year)
                    ->sum('payout'),
                'total_projected_earnings' => $user->assignedTasks()->where('status', 'in_progress')->sum('payout'),
            ];

            $data['chartData'] = $days->map(function ($date) use ($user) {
                return [
                    'date' => $date->format('M d'),
                    'completed' => Task::where('professional_id', $user->id)
                        ->where('status', 'completed')
                        ->whereDate('completed_at', $date)
                        ->count(),
                    'pending_accepted' => Task::where('professional_id', $user->id)
                        ->where('status', '!=', 'completed')
                        ->whereDate('updated_at', $date) // Assuming update means accepted or modified
                        ->count(),
                ];
            });
        }

        return Inertia::render('Dashboard', $data);
    }
}
