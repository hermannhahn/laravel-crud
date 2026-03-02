<?php

namespace App\Http\Controllers;

use App\Models\TaskArea;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class TaskAreaController extends Controller
{
    public function index(): Response
    {
        $areas = Auth::user()->taskAreas()->latest()->get();

        return Inertia::render('Company/Areas', [
            'areas' => $areas,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        Auth::user()->taskAreas()->create($validated);

        return redirect()->back()->with('message', 'Area created successfully.');
    }

    public function destroy(TaskArea $area): RedirectResponse
    {
        if ($area->company_id !== Auth::id()) {
            abort(403);
        }

        $area->delete();

        return redirect()->back()->with('message', 'Area deleted successfully.');
    }
}
