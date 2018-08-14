<?php

namespace App\Http\Controllers;

use App\Channel;
use App\Filters\ThreadsFilters;
use App\Thread;
use Illuminate\Http\Request;

class ThreadController extends Controller
{
    public function __construct()
    {
//        $this->middleware('auth')->only('store'); // 白名单，意味着仅 store 方法需要登录
        $this->middleware('auth')->except(['index','show']); // 黑名单，意味着除了这些路由都需要验证身份
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Channel $channel,ThreadsFilters $filters)
    {
        $threads = $this->getThreads($channel, $filters);

        if(request()->wantsJson()){
            return $threads;
        }

        return view('threads.index',compact('threads'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('threads.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request,[
            'title'         => 'required',
            'body'          => 'required',
            'channel_id'    => 'required|exists:channels,id'
        ]);

        $thread = Thread::create([
            'user_id'       => auth()->id(),
            'channel_id'    => request('channel_id'),
            'title'         => request('title'),
            'body'          => request('body'),
        ]);

        return redirect($thread->path());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Thread  $thread
     * @return \Illuminate\Http\Response
     */
    public function show($channelId,Thread $thread)
    {
        return view('threads.show',[
            'thread'    => $thread,
            'replies'   => $thread->replies()->paginate(10)
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Thread  $thread
     * @return \Illuminate\Http\Response
     */
    public function edit(Thread $thread)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Thread  $thread
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Thread $thread)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Thread  $thread
     * @return \Illuminate\Http\Response
     */
    public function destroy(Thread $thread)
    {
        //
    }

    protected function getThreads(Channel $channel, ThreadsFilters $filters)
    {
        $threads = Thread::latest()->filter($filters);

        if ($channel->exists) {
            $threads->where('channel_id', $channel->id);
        }

        $threads = $threads->get();

        return $threads;
    }
}
