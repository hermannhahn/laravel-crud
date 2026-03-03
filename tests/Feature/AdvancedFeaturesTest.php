<?php

use App\Models\Permission;
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

test('company cannot exceed monthly task limit', function () {
    // Create permission with limit of 2
    Permission::create([
        'user_id' => $this->company->id,
        'module' => 'tasks',
        'can_create' => true,
        'monthly_limit' => 2,
    ]);

    // Create 2 tasks
    Task::factory()->count(2)->create([
        'company_id' => $this->company->id,
        'profession_id' => $this->profession->id,
        'service_id' => $this->service->id,
    ]);

    // Try to create the 3rd task
    $taskData = [
        'profession_id' => $this->profession->id,
        'service_id' => $this->service->id,
        'description' => 'Should fail',
        'due_date' => now()->addDays(7)->format('Y-m-d'),
        'status' => 'pending',
    ];

    $this->actingAs($this->company)
        ->post(route('tasks.store'), $taskData)
        ->assertRedirect()
        ->assertSessionHas('error', 'Monthly task limit reached.');

    $this->assertEquals(2, Task::where('company_id', $this->company->id)->count());
});

test('admin can deactivate a user', function () {
    $userToDeactivate = User::factory()->professional()->create(['is_active' => true]);

    $this->actingAs($this->admin)
        ->patch(route('users.toggle-status', $userToDeactivate))
        ->assertRedirect();

    $this->assertFalse($userToDeactivate->fresh()->is_active);
});

test('admin can update a user role', function () {
    $user = User::factory()->professional()->create(['role' => 'user']);

    $this->actingAs($this->admin)
        ->patch(route('users.update-role', $user), ['role' => 'admin'])
        ->assertRedirect();

    $this->assertEquals('admin', $user->fresh()->role);
});

test('inactive user is logged out by middleware', function () {
    $user = User::factory()->professional()->create(['is_active' => false]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertRedirect(route('login'))
        ->assertSessionHas('error', 'Your account is inactive. Please contact the administrator.');
});

test('can search tasks by title or company name', function () {
    $task1 = Task::factory()->create([
        'title' => 'Specific Unique Title',
        'company_id' => $this->company->id,
    ]);
    
    $otherCompany = User::factory()->company()->create(['name' => 'Searchable Company']);
    $task2 = Task::factory()->create([
        'title' => 'Normal Task',
        'company_id' => $otherCompany->id,
    ]);

    // Search by title
    $this->actingAs($this->admin)
        ->get(route('tasks.index', ['search' => 'Specific Unique']))
        ->assertInertia(fn ($page) => $page
            ->has('tasks.data', 1)
            ->where('tasks.data.0.id', $task1->id)
        );

    // Search by company name
    $this->actingAs($this->admin)
        ->get(route('tasks.index', ['search' => 'Searchable Company']))
        ->assertInertia(fn ($page) => $page
            ->has('tasks.data', 1)
            ->where('tasks.data.0.id', $task2->id)
        );
});

test('professional can post a response to a task', function () {
    $task = Task::factory()->create([
        'company_id' => $this->company->id,
        'professional_id' => $this->professional->id,
    ]);

    $this->actingAs($this->professional)
        ->post(route('tasks.respond', $task), ['message' => 'Hello, update here!'])
        ->assertRedirect();

    $this->assertDatabaseHas('task_responses', [
        'task_id' => $task->id,
        'user_id' => $this->professional->id,
        'message' => 'Hello, update here!',
    ]);
});

test('professional can release a task', function () {
    $task = Task::factory()->create([
        'company_id' => $this->company->id,
        'professional_id' => $this->professional->id,
        'status' => 'in_progress',
    ]);

    $this->actingAs($this->professional)
        ->post(route('tasks.release', $task))
        ->assertRedirect();

    $this->assertDatabaseHas('tasks', [
        'id' => $task->id,
        'professional_id' => null,
        'status' => 'pending',
    ]);
});

test('company can authorize a professional and assign professions', function () {
    $newProfessional = User::factory()->professional()->create();
    $newProfession = Profession::factory()->create(['company_id' => $this->company->id]);

    $this->actingAs($this->company)
        ->post(route('professionals.add'), [
            'professional_id' => $newProfessional->id,
            'profession_ids' => [$newProfession->id],
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('company_professional', [
        'company_id' => $this->company->id,
        'professional_id' => $newProfessional->id,
    ]);

    $this->assertDatabaseHas('company_professional_profession', [
        'company_id' => $this->company->id,
        'professional_id' => $newProfessional->id,
        'profession_id' => $newProfession->id,
    ]);
});
