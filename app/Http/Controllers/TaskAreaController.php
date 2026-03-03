<?php

namespace App\Http\Controllers;

use App\Models\TaskArea;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class TaskAreaController extends Controller
{
    public function index(): Response
    {
        $user = Auth::user();
        
        if ($user->isAdmin()) {
            $areas = TaskArea::with('company:id,name')->latest()->get();
        } else {
            $areas = $user->taskAreas()->latest()->get();
        }

        $companies = $user->isAdmin() 
            ? User::where('user_type', 'company')->get(['id', 'name'])
            : [];

        return Inertia::render('Company/Areas', [
            'areas' => $areas,
            'companies' => $companies,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'company_id' => [$user->isAdmin() ? 'required' : 'nullable', 'exists:users,id'],
        ]);

        $companyId = $user->isAdmin() ? $validated['company_id'] : $user->id;

        TaskArea::create([
            'name' => $validated['name'],
            'company_id' => $companyId,
        ]);

        return redirect()->back()->with('message', 'Area created successfully.');
    }

    public function destroy(TaskArea $area): RedirectResponse
    {
        if (!Auth::user()->isAdmin() && $area->company_id !== Auth::id()) {
            abort(403);
        }

        $area->delete();

        return redirect()->back()->with('message', 'Area deleted successfully.');
    }
}
