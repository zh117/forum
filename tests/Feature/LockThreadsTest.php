<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class LockThreadsTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function non_administrator_may_not_lock_threads()
    {
        // 开启
        $this->withExceptionHandling();

        $this->signIn();

        $thread = create('App\Thread',[
            'user_id' => auth()->id()
        ]);

        // 更改
        $this->post(route('locked-threads.store',$thread))->assertStatus(403);

        $this->assertFalse(!! $thread->fresh()->locked);
    }

    /** @test */
    public function administrators_can_lock_threads()
    {
        $this->signIn(factory('App\User')->states('administrator')->create());

        $thread = create('App\Thread',['user_id' => auth()->id()]);

        // 更改
        $this->post(route('locked-threads.store',$thread));

        $this->assertTrue(!! $thread->fresh()->locked);
    }

    /** @test */
    public function administrators_can_unlock_threads()
    {
        $this->signIn(factory('App\User')->states('administrator')->create());

        $thread = create('App\Thread',['user_id' => auth()->id(),'locked' => true]);

        $this->delete(route('locked-threads.destroy',$thread));

        $this->assertFalse($thread->fresh()->locked);
    }

    /** @test */
    public function once_locked_thread_may_not_receive_new_replies()
    {
        $this->signIn();

        // 注意，我们在此处进行了修改
        $thread = create('App\Thread',['locked' =>true]);

        $this->post($thread->path() . '/replies',[
            'body' => 'Foobar',
            'user_id' => auth()->id()
        ])->assertStatus(422);
    }
}