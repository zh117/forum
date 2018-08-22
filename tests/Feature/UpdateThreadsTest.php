<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateThreadsTest extends TestCase
{
    use RefreshDatabase;

    public function setUp()
    {
        parent::setUp();
        // 每个测试都需要用到以下操作
        $this->withExceptionHandling();

        $this->signIn();
    }

    /** @test */
    public function unauthorized_users_may_not_update_threads() // 只有话题创建者才能更新
    {
        $thread = create('App\Thread',['user_id' => create('App\User')->id]);

        $this->patch($thread->path(),[])->assertStatus(403);
    }

    /** @test */
    public function a_thread_requires_a_title_and_body_to_be_updated() // 更新的字段要符合规则
    {
        $thread = create('App\Thread',['user_id' => auth()->id()]);

        $this->patch($thread->path(),[
            'title' => 'Changed.'
        ])->assertSessionHasErrors('body');

        $this->patch($thread->path(),[
            'body' => 'Changed.'
        ])->assertSessionHasErrors('title');
    }

    /** @test */
    public function a_thread_can_be_updated_by_its_creator() // 话题可以成功更新
    {
        $thread = create('App\Thread',['user_id' => auth()->id()]);

        $this->patch($thread->path(),[
            'title' => 'Changed.',
            'body' => 'Changed body.'
        ]);

        tap($thread->fresh(),function ($thread) {
            $this->assertEquals('Changed.',$thread->title);
            $this->assertEquals('Changed body.',$thread->body);
        });
    }
}