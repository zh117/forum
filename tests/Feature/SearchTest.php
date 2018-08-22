<?php

namespace Tests\Feature;

use App\Thread;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_user_can_search_threads()
    {
        // 启用驱动
        config(['scout.driver' => 'algolia']);

        $search = 'foobar';

        create('App\Thread',[],2);
        create('App\Thread',['body' => "A thread with {$search} term."],2);

        // 由于网络等因素，所以我们进行以下处理
        do {
            sleep(.25);

            $results = $this->getJson("/threads/search?q={$search}")->json();
        } while (empty($results));

        $this->assertCount(2,$results['data']);

        // 删除测试数据
        Thread::latest()->take(4)->unsearchable();
    }
}