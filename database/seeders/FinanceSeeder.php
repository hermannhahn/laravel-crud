<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\User;
use App\Models\Service;
use App\Models\Profession;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class FinanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $company = User::where('user_type', 'company')->first();
        $professional = User::where('user_type', 'professional')->first();
        
        if (!$company || !$professional) return;

        $professions = Profession::all();
        $services = Service::all();

        if ($services->isEmpty()) return;

        // Create 10 completed tasks for finance records
        for ($i = 0; $i < 10; $i++) {
            $service = $services->random();
            $completedAt = Carbon::now()->subDays(rand(0, 30));
            
            Task::create([
                'user_id' => $company->id,
                'company_id' => $company->id,
                'professional_id' => $professional->id,
                'profession_id' => $service->profession_id,
                'service_id' => $service->id,
                'title' => $service->title . ' (Seeded)',
                'description' => 'Completed task for finance report.',
                'status' => 'completed',
                'payout' => $service->payout,
                'completed_at' => $completedAt,
                'due_date' => $completedAt->copy()->subDays(2),
            ]);
        }
    }
}
