<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;

class FinanceController extends Controller
{
    public function index(Request $request): Response
    {
        $user = Auth::user();
        $query = Task::with(['company:id,name', 'professional:id,name', 'service:id,title'])
            ->where('status', 'completed');

        // Scoping
        if ($user->isAdmin()) {
            // All completed tasks
        } elseif ($user->isCompany()) {
            $query->where('company_id', $user->id);
        } else {
            $query->where('professional_id', $user->id);
        }

        // Filters
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhereHas('professional', fn($sub) => $sub->where('name', 'like', '%' . $request->search . '%'))
                  ->orWhereHas('company', fn($sub) => $sub->where('name', 'like', '%' . $request->search . '%'));
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('completed_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('completed_at', '<=', $request->date_to);
        }

        // Sorting
        $query->orderBy('completed_at', 'desc');

        $tasks = $query->paginate(15)->withQueryString();

        // Totals
        $totalAmount = (clone $query)->sum('payout');
        
        $stats = [
            'total_amount' => $totalAmount,
            'count' => (clone $query)->count(),
        ];

        return Inertia::render('Finance/Index', [
            'tasks' => $tasks,
            'filters' => $request->only(['search', 'date_from', 'date_to']),
            'stats' => $stats,
        ]);
    }
}
