<?php

namespace Tests\Feature;

use App\Activity;
use App\Rules\Recaptcha;
use App\Thread;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
class CreateThreadsTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp()
    {
        parent::setUp();

        // 默认情况下，Recaptcha 验证规则都会通过
        app()->singleton(Recaptcha::class,function() {
            return \Mockery::mock(Recaptcha::class,function ($m) {
                $m->shouldReceive('passes')->andReturn(true);
            });
        });
    }

    /** @test */
    public function guests_may_not_create_threads()
    {
        $this->withExceptionHandling();

        $this->get('/threads/create')
            ->assertRedirect(route('login')); // 应用路由命名

        $this->post(route('threads')) // 应用路由命名
        ->assertRedirect(route('login')); // 应用路由命名
    }

    // 修改测试命名，更加辨识度
    /** @test */
    public function new_users_must_first_confirm_their_email_address_before_creating_threads()
    {
        // 调用 unconfirmed，生成未认证用户
        $user = factory('App\User')->states('unconfirmed')->create();

        $this->signIn($user);

        $thread = make('App\Thread');

        $this->post(route('threads'),$thread->toArray())
            ->assertRedirect('/threads')
            ->assertSessionHas('flash','You must first confirm your email address.');
    }

    // 修改测试命名，更加辨识度
    /** @test */
    public function a_user_can_create_new_forum_threads()
    {
        $this->signIn();

        $thread = make('App\Thread');
        // 添加 g-recaptcha-response 即可通过验证
        $response = $this->post(route('threads'),$thread->toArray() + ['g-recaptcha-response' => 'token']);

        $this->get($response->headers->get('Location'))
            ->assertSee($thread->title)
            ->assertSee($thread->body);
    }

    /** @test */
    public function a_thread_requires_a_title()
    {
        $this->publishThread(['title' => null])
            ->assertSessionHasErrors('title');
    }

    /** @test */
    public function a_thread_requires_a_body()
    {
        $this->publishThread(['body' => null])
            ->assertSessionHasErrors('body');
    }

    /** @test */
    public function a_thread_requires_recaptcha_verification()
    {
        unset(app()[Recaptcha::class]);

        // 添加 g-recaptcha-response 即可通过验证
        $this->publishThread(['g-recaptcha-response' => 'test'])
            ->assertSessionHasErrors('g-recaptcha-response');
    }

    /** @test */
    public function a_thread_requires_a_valid_channel()
    {
        factory('App\Channel',2)->create(); // 新建两个 Channel，id 分别为 1 跟 2

        $this->publishThread(['channel_id' => null])
            ->assertSessionHasErrors('channel_id');

        $this->publishThread(['channel_id' => 999])  // channle_id 为 999，是一个不存在的 Channel
        ->assertSessionHasErrors('channel_id');
    }

    /** @test */
    public function a_thread_with_a_title_that_ends_in_a_number_should_generate_the_proper_slug()
    {
        $this->signIn();

        $thread = create('App\Thread',['title' => 'Something 24']);

        // 添加 g-recaptcha-response 即可通过验证
        $thread = $this->postJson(route('threads'),$thread->toArray() + ['g-recaptcha-response' => 'token'])->json();

        $this->assertEquals("something-24-{$thread['id']}",$thread['slug']);
    }

    /** @test */
    public function unauthorized_users_may_not_delete_threads()
    {
        $this->withExceptionHandling();

        $thread = create('App\Thread');

        $this->delete($thread->path())->assertRedirect(route('login')); // 应用路由命名

        $this->signIn();
        $this->delete($thread->path())->assertStatus(403);
    }

    /** @test */
    public function authorized_users_can_delete_threads()
    {
        $this->signIn();

        $thread = create('App\Thread',['user_id' => auth()->id()]);
        $reply = create('App\Reply',['thread_id' => $thread->id]);

        $response =  $this->json('DELETE',$thread->path());

        $response->assertStatus(204);

        $this->assertDatabaseMissing('threads',['id' => $thread->id]);
        $this->assertDatabaseMissing('replies',['id' => $reply->id]);

        $this->assertEquals(0,Activity::count());
    }

    public function publishThread($overrides = [])
    {
        $this->withExceptionHandling()->signIn();

        $thread = make('App\Thread',$overrides);

        return $this->post(route('threads'),$thread->toArray()); // 应用路由命名
    }

    /** @test */
    public function a_thread_requires_a_unique_slug()
    {
        $this->signIn();

        create('App\Thread',[],2);

        $thread = create('App\Thread',['title' => 'Foo Title']);

        $this->assertEquals($thread->fresh()->slug,'foo-title');

        // 添加 g-recaptcha-response 即可通过验证
        $thread = $this->postJson(route('threads'),$thread->toArray() + ['g-recaptcha-response' => 'token'])->json();

        $this->assertEquals("foo-title-{$thread['id']}",$thread['slug']);
    }
}