<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

class SettingController extends Controller
{
    /**
     * Display the settings page (Admin only).
     */
    public function index(): Response
    {
        return Inertia::render('Admin/Settings', [
            'settings' => [
                'app_name' => Setting::get('app_name', config('app.name')),
                'app_logo' => Setting::get('app_logo'),
                'help_content' => Setting::get('help_content', ''),
            ]
        ]);
    }

    /**
     * Update system settings.
     */
    public function update(Request $request): RedirectResponse
    {
        Log::info('Settings update request received', $request->all());

        $request->validate([
            'app_name' => 'nullable|string|max:255',
            'app_logo' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
            'help_content' => 'nullable|string',
        ]);

        if ($request->has('app_name')) {
            Log::info('Updating app_name: ' . $request->app_name);
            Setting::set('app_name', $request->app_name);
        }

        if ($request->hasFile('app_logo')) {
            Log::info('Updating app_logo');
            // Delete old logo if exists
            $oldLogo = Setting::get('app_logo');
            if ($oldLogo) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $oldLogo));
            }

            $path = $request->file('app_logo')->store('settings', 'public');
            Setting::set('app_logo', '/storage/' . $path);
        }

        if ($request->has('help_content')) {
            Log::info('Updating help_content');
            Setting::set('help_content', $request->help_content);
        }

        return redirect()->back()->with('message', 'Settings updated successfully.');
    }

    /**
     * Display the help page for all users.
     */
    public function help(): Response
    {
        return Inertia::render('Help', [
            'content' => Setting::get('help_content', 'No help information available at the moment.')
        ]);
    }
}
