<?php

namespace App\Http\Controllers;

use App\Models\Task;
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
            $data['stats'] = [
                'pending' => Task::where('status', '!=', 'completed')->count(),
                'completed' => Task::where('status', 'completed')->count(),
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
