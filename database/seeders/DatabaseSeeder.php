<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Profession;
use App\Models\Service;
use App\Models\Task;
use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Initial Settings
        Setting::set('app_name', 'HeartLife Platform');
        Setting::set('admin_commission_percentage', 15);
        Setting::set('task_label_singular', 'Order');
        Setting::set('task_label_plural', 'Orders');

        // 2. Create Admin (First User)
        $admin = User::create([
            'name' => 'System Admin',
            'email' => 'admin@admin',
            'password' => Hash::make('admin'),
            'role' => 'admin',
            'user_type' => 'company',
            'is_active' => true,
        ]);

        // 3. Create Companies
        $companies = [];
        for ($i = 1; $i <= 2; $i++) {
            $companies[] = User::create([
                'name' => "Company $i Corp",
                'email' => "company$i@test.com",
                'password' => Hash::make('password'),
                'role' => 'user',
                'user_type' => 'company',
                'is_active' => true,
            ]);
        }

        // 4. Create Professionals
        $professionals = [];
        for ($i = 1; $i <= 5; $i++) {
            $professionals[] = User::create([
                'name' => "Professional $i",
                'email' => "pro$i@test.com",
                'password' => Hash::make('password'),
                'role' => 'user',
                'user_type' => 'professional',
                'is_active' => true,
            ]);
        }

        // 5. Create Professions & Services
        $professionNames = ['Backend', 'Frontend', 'Design', 'QA', 'DevOps'];
        $allServices = [];

        foreach ($companies as $company) {
            foreach ($professionNames as $name) {
                $profession = Profession::create([
                    'name' => $name,
                    'company_id' => $company->id,
                ]);

                // 5 Services per Profession
                for ($j = 1; $j <= 5; $j++) {
                    $allServices[] = Service::create([
                        'company_id' => $company->id,
                        'profession_id' => $profession->id,
                        'title' => "$name Service $j",
                        'description' => "Standard $name quality service level $j.",
                        'payout' => rand(50, 500),
                    ]);
                }
            }
        }

        // 6. Authorize Professionals
        foreach ($professionals as $pro) {
            foreach ($companies as $company) {
                // Link Professional to Company with permissions
                \Illuminate\Support\Facades\DB::table('company_professional')->insert([
                    'company_id' => $company->id,
                    'professional_id' => $pro->id,
                    'can_view_tasks' => true,
                    'can_respond_tasks' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Link Professional to 2 random professions from that company
                $randomProfessions = Profession::where('company_id', $company->id)->inRandomOrder()->take(2)->get();
                foreach ($randomProfessions as $prof) {
                    \Illuminate\Support\Facades\DB::table('company_professional_profession')->insert([
                        'company_id' => $company->id,
                        'professional_id' => $pro->id,
                        'profession_id' => $prof->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        // 7. Create Tasks
        $statuses = ['pending', 'in_progress', 'completed'];
        for ($i = 1; $i <= 10; $i++) {
            $service = collect($allServices)->random();
            $status = $statuses[array_rand($statuses)];
            $pro = ($status !== 'pending') ? collect($professionals)->random() : null;
            
            $completedAt = ($status === 'completed') ? Carbon::now()->subDays(rand(1, 10)) : null;

            Task::create([
                'user_id' => $service->company_id,
                'company_id' => $service->company_id,
                'profession_id' => $service->profession_id,
                'service_id' => $service->id,
                'professional_id' => $pro?->id,
                'title' => $service->title,
                'description' => "Execution of " . $service->title,
                'status' => $status,
                'payout' => $service->payout,
                'due_date' => Carbon::now()->addDays(rand(5, 15)),
                'completed_at' => $completedAt,
            ]);
        }
    }
}
