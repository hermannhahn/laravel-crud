<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\TaskArea;
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
        
        // If admin, they might want to filter by company
        $targetCompanyId = $user->isAdmin() ? $request->company_id : $user->id;

        $servicesQuery = Service::with(['area', 'company:id,name']);

        if ($targetCompanyId) {
            $servicesQuery->where('company_id', $targetCompanyId);
        }

        $services = $servicesQuery->latest()->get();

        // Areas for the creation form
        $areas = [];
        if ($targetCompanyId) {
            $areas = TaskArea::where('company_id', $targetCompanyId)->get(['id', 'name']);
        } elseif ($user->isAdmin()) {
            $areas = TaskArea::all(['id', 'name']);
        }

        $companies = $user->isAdmin()
            ? User::where('user_type', 'company')->get(['id', 'name'])
            : [];

        return Inertia::render('Company/Services', [
            'services' => $services,
            'areas' => $areas,
            'companies' => $companies,
            'filters' => $request->only(['company_id']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'task_area_id' => ['required', 'exists:task_areas,id'],
            'company_id' => [$user->isAdmin() ? 'required' : 'nullable', 'exists:users,id'],
        ]);

        $companyId = $user->isAdmin() ? $validated['company_id'] : $user->id;

        Service::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'price' => $validated['price'],
            'task_area_id' => $validated['task_area_id'],
            'company_id' => $companyId,
        ]);

        return redirect()->back()->with('message', 'Service created successfully.');
    }

    public function destroy(Service $service): RedirectResponse
    {
        if (!Auth::user()->isAdmin() && $service->company_id !== Auth::id()) {
            abort(403);
        }

        $service->delete();

        return redirect()->back()->with('message', 'Service deleted successfully.');
    }
}
