<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class CompanyProfessionalController extends Controller
{
    public function index(Request $request): Response
    {
        $user = Auth::user();
        $professionals = [];
        $areas = [];
        
        $targetCompanyId = $user->isAdmin() ? $request->company_id : $user->id;

        if ($targetCompanyId) {
            $company = User::find($targetCompanyId);
            if ($company && ($user->isAdmin() || $company->id === $user->id)) {
                $professionals = $company->professionals()
                    ->with(['companyAreas' => function($query) use ($company) {
                        $query->where('company_professional_area.company_id', $company->id);
                    }])
                    ->withCount(['assignedTasks' => function($query) use ($company) {
                        $query->where('company_id', $company->id);
                    }])
                    ->get();
                
                $areas = $company->taskAreas()->get(['id', 'name']);
            }
        }

        $companies = $user->isAdmin()
            ? User::where('user_type', 'company')->get(['id', 'name'])
            : [];

        return Inertia::render('Company/Professionals', [
            'professionals' => $professionals,
            'areas' => $areas,
            'companies' => $companies,
        ]);
    }

    public function addProfessional(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $request->validate([
            'professional_id' => ['required', 'numeric', 'exists:users,id'],
            'task_area_ids' => ['required', 'array'],
            'task_area_ids.*' => ['exists:task_areas,id'],
            'company_id' => [$user->isAdmin() ? 'required' : 'nullable', 'exists:users,id'],
        ]);

        $companyId = $user->isAdmin() ? $request->company_id : $user->id;
        $company = User::findOrFail($companyId);
        $professional = User::findOrFail($request->professional_id);

        if ($professional->user_type !== 'professional') {
            abort(400, 'User is not a professional.');
        }

        $company->professionals()->syncWithoutDetaching([$professional->id]);
        
        // Link to multiple areas
        $areaData = [];
        foreach ($request->task_area_ids as $areaId) {
            $areaData[$areaId] = ['company_id' => $company->id];
        }
        $professional->companyAreas()->wherePivot('company_id', $company->id)->sync($areaData);

        return redirect()->back()->with('message', 'Professional authorized successfully.');
    }

    public function removeProfessional(Request $request, User $user): RedirectResponse
    {
        $authUser = Auth::user();
        $companyId = $authUser->isAdmin() ? $request->company_id : $authUser->id;
        
        if (!$companyId) abort(400, 'Company ID is required.');

        $company = User::findOrFail($companyId);
        $company->professionals()->detach($user->id);
        
        // Cleanup areas
        $user->companyAreas()->wherePivot('company_id', $company->id)->detach();

        return redirect()->back()->with('message', 'Professional deauthorized.');
    }

    public function updatePermissions(Request $request, User $user): RedirectResponse
    {
        $authUser = Auth::user();
        $validated = $request->validate([
            'task_area_ids' => ['required', 'array'],
            'task_area_ids.*' => ['exists:task_areas,id'],
            'can_view_tasks' => ['required', 'boolean'],
            'can_respond_tasks' => ['required', 'boolean'],
            'can_edit_tasks' => ['required', 'boolean'],
            'company_id' => [$authUser->isAdmin() ? 'required' : 'nullable', 'exists:users,id'],
        ]);

        $companyId = $authUser->isAdmin() ? $validated['company_id'] : $authUser->id;
        $company = User::findOrFail($companyId);
        
        // Update pivot basic permissions
        $company->professionals()->updateExistingPivot($user->id, [
            'can_view_tasks' => $validated['can_view_tasks'],
            'can_respond_tasks' => $validated['can_respond_tasks'],
            'can_edit_tasks' => $validated['can_edit_tasks'],
        ]);

        // Sync areas
        $areaData = [];
        foreach ($validated['task_area_ids'] as $areaId) {
            $areaData[$areaId] = ['company_id' => $company->id];
        }
        $user->companyAreas()->wherePivot('company_id', $company->id)->sync($areaData);

        return redirect()->back()->with('message', 'Professional updated successfully.');
    }
}
