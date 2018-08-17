<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ParticipateInForumTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function unauthenticated_user_may_no_add_replies()
    {
        $this->withExceptionHandling()
            ->post('threads/some-channel/1/replies',[])
            ->assertRedirect('/login');
    }

    /** @test */
    function an_authenticated_user_may_participate_in_forum_threads()
    {
        // Given we have a authenticated user
        $this->signIn();
        // And an existing thread
        $thread = create('App\Thread');

        // When the user adds a reply to the thread
        $reply = make('App\Reply');
        $this->post($thread->path() .'/replies',$reply->toArray());

        // Then their reply should be visible on the page
//        $this->get($thread->path())
//            ->assertSee($reply->body);

        $this->assertDatabaseHas('replies',['body' => $reply->body]);
        $this->assertEquals(1,$thread->fresh()->replies_count);
    }

    /** @test */
    public function a_reply_requires_a_body()
    {
        $this->withExceptionHandling()->signIn();

        $thread = create('App\Thread');
        $reply = make('App\Reply',['body' => null]);

        $this->post($thread->path() . '/replies',$reply->toArray())
            ->assertSessionHasErrors('body');
    }

    /** @test */
    public function unauthorized_users_cannot_delete_replies()
    {
        $this->withExceptionHandling();

        $reply = create('App\Reply');

        $this->delete("/replies/{$reply->id}")
            ->assertRedirect('login');

        $this->signIn()
            ->delete("/replies/{$reply->id}")
            ->assertStatus(403);
    }

    /** @test */
    public function authorized_users_can_delete_replies()
    {
        $this->signIn();

        $reply = create('App\Reply',['user_id' => auth()->id()]);

        $this->delete("/replies/{$reply->id}")->assertStatus(302);

        $this->assertDatabaseMissing('replies',['id' => $reply->id]);
        $this->assertEquals(0,$reply->thread->fresh()->replies_count);
    }

    /** @test */
    public function unauthorized_users_cannot_update_replies()
    {
        $this->withExceptionHandling();

        $reply = create('App\Reply');

        $this->patch("/replies/{$reply->id}")
            ->assertRedirect('login');

        $this->signIn()
            ->patch("/replies/{$reply->id}")
            ->assertStatus(403);
    }

    /** @test */
    public function authorized_users_can_update_replies()
    {
        $this->signIn();

        $reply = create('App\Reply',['user_id' => auth()->id()]);

        $updatedReply = 'You have been changed,foo.';
        $this->patch("/replies/{$reply->id}",['body' => $updatedReply]);

        $this->assertDatabaseHas('replies',['id' => $reply->id,'body' => $updatedReply]);
    }
}
