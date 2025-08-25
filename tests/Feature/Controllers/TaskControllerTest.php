<?php

namespace Tests\Feature\Controllers;

use Tests\TestCase;
use App\Models\User;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TaskControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_get_all_tasks()
    {
        $user = User::factory()->create();
        $tasks = Task::factory()->count(3)->create(['user_id' => $user->id]);
        
        $response = $this->get('/tasks');
        
        $response->assertStatus(200);
        $response->assertJsonCount(3);
        
        foreach ($tasks as $task) {
            $response->assertJsonFragment([
                'id' => $task->id,
                'user_id' => $task->user_id,
            ]);
        }
    }

    /** @test */
    public function can_get_tasks_when_no_tasks_exist()
    {
        $response = $this->get('/tasks');
        
        $response->assertStatus(200);
        $response->assertJsonCount(0);
        $response->assertExactJson([]);
    }

    /** @test */
    public function task_index_returns_json_response()
    {
        Task::factory()->create();
        
        $response = $this->get('/tasks');
        
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/json');
    }

    /** @test */
    public function task_model_has_required_relationships()
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id]);
        
        // Test relationship
        $this->assertInstanceOf(User::class, $task->user);
        $this->assertEquals($user->id, $task->user->id);
    }

    /** @test */
    public function tasks_belong_to_correct_users()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        $task1 = Task::factory()->create(['user_id' => $user1->id]);
        $task2 = Task::factory()->create(['user_id' => $user2->id]);
        
        $response = $this->get('/tasks');
        
        $response->assertStatus(200);
        $response->assertJsonCount(2);
        
        $tasks = $response->json();
        
        $this->assertEquals($user1->id, collect($tasks)->where('id', $task1->id)->first()['user_id']);
        $this->assertEquals($user2->id, collect($tasks)->where('id', $task2->id)->first()['user_id']);
    }

    // Note: Các method khác trong TaskController chưa được implement nên không test
    // Khi các method được implement, có thể thêm tests cho:
    // - store() method
    // - show() method  
    // - update() method
    // - destroy() method
    // - create() method
    // - edit() method
}
