<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class CompanyTeamController extends Controller
{
    public function index(): Response
    {
        $company = Auth::user();
        
        $team = $company->professionals()
            ->withCount(['assignedTasks' => function($query) use ($company) {
                $query->where('company_id', $company->id);
            }])
            ->get();

        $areas = $company->taskAreas()->get(['id', 'name']);

        // Professionals NOT yet in this company team
        $availableProfessionals = User::where('user_type', 'professional')
            ->whereDoesntHave('companies', function($query) use ($company) {
                $query->where('company_id', $company->id);
            })
            ->get(['id', 'name', 'email']);

        return Inertia::render('Company/Team', [
            'team' => $team,
            'availableProfessionals' => $availableProfessionals,
            'areas' => $areas,
        ]);
    }

    public function addProfessional(Request $request): RedirectResponse
    {
        $request->validate([
            'professional_id' => ['required', 'exists:users,id'],
            'task_area_id' => ['required', 'exists:task_areas,id'],
        ]);

        $company = Auth::user();
        $professional = User::findOrFail($request->professional_id);

        if ($professional->user_type !== 'professional') {
            abort(400, 'User is not a professional.');
        }

        $company->professionals()->syncWithoutDetaching([
            $professional->id => ['task_area_id' => $request->task_area_id]
        ]);

        return redirect()->back()->with('message', 'Professional added to team.');
    }

    public function removeProfessional(User $user): RedirectResponse
    {
        $company = Auth::user();
        $company->professionals()->detach($user->id);

        return redirect()->back()->with('message', 'Professional removed from team.');
    }

    public function updatePermissions(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'task_area_id' => ['required', 'exists:task_areas,id'],
            'can_view_tasks' => ['required', 'boolean'],
            'can_respond_tasks' => ['required', 'boolean'],
            'can_edit_tasks' => ['required', 'boolean'],
        ]);

        $company = Auth::user();
        $company->professionals()->updateExistingPivot($user->id, $validated);

        return redirect()->back()->with('message', 'Team member updated.');
    }
}
