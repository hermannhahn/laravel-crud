<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;

use App\Models\Setting;

class FinanceController extends Controller
{
    public function index(Request $request): Response
    {
        $user = Auth::user();
        $query = Task::with(['company:id,name', 'professional:id,name', 'service:id,title'])
            ->whereIn('status', ['completed', 'in_progress']);

        // Scoping
        if ($user->isAdmin()) {
            // All relevant tasks
        } elseif ($user->isCompany()) {
            $query->where('company_id', $user->id);
        } else {
            $query->where('professional_id', $user->id);
        }

        // --- FILTERS ---
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhereHas('professional', fn($sub) => $sub->where('name', 'like', '%' . $request->search . '%'))
                  ->orWhereHas('company', fn($sub) => $sub->where('name', 'like', '%' . $request->search . '%'));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            // Use created_at for in_progress or completed_at for completed? 
            // Standardizing to using the relevant date based on context or just a general date filter.
            // Let's use created_at as the primary temporal reference for "financial events" unless completed.
            $query->whereDate($request->status === 'completed' ? 'completed_at' : 'created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate($request->status === 'completed' ? 'completed_at' : 'created_at', '<=', $request->date_to);
        }

        // Sorting
        $query->orderBy('updated_at', 'desc');

        // --- STATS CALCULATION (Independent of pagination but respecting other filters) ---
        $statsQuery = clone $query;
        
        $commissionPercent = (float) Setting::get('admin_commission_percentage', 0);

        $completedValue = (clone $statsQuery)->where('status', 'completed')->sum('payout');
        $inProgressValue = (clone $statsQuery)->where('status', 'in_progress')->sum('payout');

        if ($user->isAdmin()) {
            // Admin sees their commission portion
            $completedValue = ($completedValue * $commissionPercent) / 100;
            $inProgressValue = ($inProgressValue * $commissionPercent) / 100;
        }

        $stats = [
            'completed_amount' => $completedValue,
            'in_progress_amount' => $inProgressValue,
            'total_count' => (clone $statsQuery)->count(),
            'commission_rate' => $commissionPercent,
        ];

        $tasks = $query->paginate(15)->withQueryString();

        return Inertia::render('Finance/Index', [
            'tasks' => $tasks,
            'filters' => $request->only(['search', 'date_from', 'date_to', 'status']),
            'stats' => $stats,
        ]);
    }
}
