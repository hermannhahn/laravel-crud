<?php

namespace App\Http\Controllers;

use App\Models\Profession;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ProfessionController extends Controller
{
    public function index(Request $request): Response
    {
        $user = Auth::user();
        
        $query = Profession::with('company:id,name');

        if ($user->isAdmin()) {
            if ($request->filled('company_id')) {
                $query->where('company_id', $request->company_id);
            }
        } else {
            $query->where('company_id', $user->id);
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $professions = $query->latest()->paginate(15)->withQueryString();

        $companies = $user->isAdmin() 
            ? User::where('user_type', 'company')->get(['id', 'name'])
            : [];

        return Inertia::render('Company/Professions/Index', [
            'professions' => $professions,
            'companies' => $companies,
            'filters' => $request->only(['company_id', 'search']),
        ]);
    }

    public function create(): Response
    {
        $user = Auth::user();
        $companies = $user->isAdmin() 
            ? User::where('user_type', 'company')->get(['id', 'name'])
            : [];

        return Inertia::render('Company/Professions/Create', [
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

        Profession::create([
            'name' => $validated['name'],
            'company_id' => $companyId,
        ]);

        return redirect()->route('professions.index')->with('message', 'Profession created successfully.');
    }

    public function edit(Profession $profession): Response
    {
        $user = Auth::user();
        if (!$user->isAdmin() && $profession->company_id !== $user->id) {
            abort(403);
        }

        return Inertia::render('Company/Professions/Edit', [
            'profession' => $profession,
        ]);
    }

    public function update(Request $request, Profession $profession): RedirectResponse
    {
        $user = Auth::user();
        if (!$user->isAdmin() && $profession->company_id !== $user->id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $profession->update($validated);

        return redirect()->route('professions.index')->with('message', 'Profession updated successfully.');
    }

    public function destroy(Profession $profession): RedirectResponse
    {
        if (!Auth::user()->isAdmin() && $profession->company_id !== Auth::id()) {
            abort(403);
        }

        $profession->delete();

        return redirect()->route('professions.index')->with('message', 'Profession deleted successfully.');
    }
}
