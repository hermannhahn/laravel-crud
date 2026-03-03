<?php

use App\Models\Profession;
use App\Models\Service;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->company = User::factory()->company()->create();
    $this->professional = User::factory()->professional()->create();
    
    $this->profession = Profession::factory()->create(['company_id' => $this->company->id]);
    $this->service = Service::factory()->create([
        'company_id' => $this->company->id,
        'profession_id' => $this->profession->id,
    ]);
});

test('professional only sees tasks from authorized companies and professions', function () {
    // Task from our company/profession
    $task1 = Task::factory()->create([
        'company_id' => $this->company->id,
        'profession_id' => $this->profession->id,
        'service_id' => $this->service->id,
        'professional_id' => null,
    ]);

    // Task from another company
    $otherCompany = User::factory()->company()->create();
    $otherProfession = Profession::factory()->create(['company_id' => $otherCompany->id]);
    $task2 = Task::factory()->create([
        'company_id' => $otherCompany->id,
        'profession_id' => $otherProfession->id,
        'professional_id' => null,
    ]);

    // Initially, professional sees NOTHING (not authorized yet)
    $this->actingAs($this->professional)
        ->get(route('tasks.index'))
        ->assertInertia(fn ($page) => $page
            ->has('tasks.data', 0)
        );

    // Authorize professional for our company and profession
    DB::table('company_professional_profession')->insert([
        'professional_id' => $this->professional->id,
        'company_id' => $this->company->id,
        'profession_id' => $this->profession->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // Now professional should see task1 but not task2
    $this->actingAs($this->professional)
        ->get(route('tasks.index'))
        ->assertInertia(fn ($page) => $page
            ->has('tasks.data', 1)
            ->where('tasks.data.0.id', $task1->id)
        );
});

test('company can create a task and it appears in their list', function () {
    $taskData = [
        'profession_id' => $this->profession->id,
        'service_id' => $this->service->id,
        'description' => 'Test task description',
        'due_date' => now()->addDays(7)->format('Y-m-d'),
        'status' => 'pending',
    ];

    $this->actingAs($this->company)
        ->post(route('tasks.store'), $taskData)
        ->assertRedirect(route('tasks.index'));

    $this->assertDatabaseHas('tasks', [
        'company_id' => $this->company->id,
        'service_id' => $this->service->id,
        'description' => 'Test task description',
    ]);
});

test('professional can accept an authorized task', function () {
    // Authorize
    DB::table('company_professional_profession')->insert([
        'professional_id' => $this->professional->id,
        'company_id' => $this->company->id,
        'profession_id' => $this->profession->id,
    ]);

    $task = Task::factory()->create([
        'company_id' => $this->company->id,
        'profession_id' => $this->profession->id,
        'service_id' => $this->service->id,
        'professional_id' => null,
        'status' => 'pending',
    ]);

    $this->actingAs($this->professional)
        ->post(route('tasks.accept', $task))
        ->assertRedirect();

    $this->assertDatabaseHas('tasks', [
        'id' => $task->id,
        'professional_id' => $this->professional->id,
        'status' => 'in_progress',
    ]);
});

test('admin can see global stats on dashboard', function () {
    Task::factory()->count(3)->create(['status' => 'pending']);
    Task::factory()->count(2)->create(['status' => 'completed', 'completed_at' => now()]);

    $this->actingAs($this->admin)
        ->get(route('dashboard'))
        ->assertInertia(fn ($page) => $page
            ->where('stats.pending', 3)
            ->where('stats.completed', 2)
        );
});

test('professional cannot accept task from unauthorized company', function () {
    $otherCompany = User::factory()->company()->create();
    $otherProfession = Profession::factory()->create(['company_id' => $otherCompany->id]);
    $task = Task::factory()->create([
        'company_id' => $otherCompany->id,
        'profession_id' => $otherProfession->id,
        'professional_id' => null,
    ]);

    $this->actingAs($this->professional)
        ->post(route('tasks.accept', $task))
        ->assertStatus(403);
});
