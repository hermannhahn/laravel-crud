<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\Profession;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ServiceController extends Controller
{
    public function index(Request $request): Response
    {
        $user = Auth::user();
        
        $targetCompanyId = $user->isAdmin() ? $request->company_id : $user->id;

        $servicesQuery = Service::with(['profession', 'company:id,name']);

        if ($targetCompanyId) {
            $servicesQuery->where('company_id', $targetCompanyId);
        }

        if ($request->filled('search')) {
            $servicesQuery->where(function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%')
                  ->orWhereHas('profession', function($sub) use ($request) {
                      $sub->where('name', 'like', '%' . $request->search . '%');
                  });
            });
        }

        $services = $servicesQuery->latest()->paginate(15)->withQueryString();

        $companies = $user->isAdmin()
            ? User::where('user_type', 'company')->get(['id', 'name'])
            : [];

        return Inertia::render('Company/Services/Index', [
            'services' => $services,
            'companies' => $companies,
            'filters' => $request->only(['company_id', 'search']),
        ]);
    }

    public function create(Request $request): Response
    {
        $user = Auth::user();
        $targetCompanyId = $user->isAdmin() ? $request->company_id : $user->id;

        $professions = [];
        if ($targetCompanyId) {
            $professions = Profession::where('company_id', $targetCompanyId)->get(['id', 'name']);
        } elseif ($user->isAdmin()) {
            $professions = Profession::all(['id', 'name']);
        }

        $companies = $user->isAdmin()
            ? User::where('user_type', 'company')->get(['id', 'name'])
            : [];

        return Inertia::render('Company/Services/Create', [
            'professions' => $professions,
            'companies' => $companies,
            'selectedCompanyId' => $targetCompanyId,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'payout' => ['required', 'numeric', 'min:0'],
            'profession_id' => ['required', 'exists:professions,id'],
            'company_id' => [$user->isAdmin() ? 'required' : 'nullable', 'exists:users,id'],
        ]);

        $companyId = $user->isAdmin() ? $validated['company_id'] : $user->id;

        Service::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'payout' => $validated['payout'],
            'profession_id' => $validated['profession_id'],
            'company_id' => $companyId,
        ]);

        return redirect()->route('services.index')->with('message', 'Service created successfully.');
    }

    public function edit(Service $service): Response
    {
        $user = Auth::user();
        if (!$user->isAdmin() && $service->company_id !== $user->id) {
            abort(403);
        }

        $professions = Profession::where('company_id', $service->company_id)->get(['id', 'name']);
        
        return Inertia::render('Company/Services/Edit', [
            'service' => $service->load('profession'),
            'professions' => $professions,
        ]);
    }

    public function update(Request $request, Service $service): RedirectResponse
    {
        $user = Auth::user();
        if (!$user->isAdmin() && $service->company_id !== $user->id) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'payout' => ['required', 'numeric', 'min:0'],
            'profession_id' => ['required', 'exists:professions,id'],
        ]);

        $service->update($validated);

        return redirect()->route('services.index')->with('message', 'Service updated successfully.');
    }

    public function destroy(Service $service): RedirectResponse
    {
        if (!Auth::user()->isAdmin() && $service->company_id !== Auth::id()) {
            abort(403);
        }

        $service->delete();

        return redirect()->route('services.index')->with('message', 'Service deleted successfully.');
    }
}
