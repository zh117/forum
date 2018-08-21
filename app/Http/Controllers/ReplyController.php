<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreatePostForm;
use App\Notifications\YouWereMentioned;
use App\Reply;
use App\Inspections\Spam;
use App\Thread;
use App\User;
use Illuminate\Http\Request;

class ReplyController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth',['except' => 'index']);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($channelId,Thread $thread)
    {
        return $thread->replies()->paginate(20);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store($channelId, Thread $thread, CreatePostForm $form)
    {
        $reply = $thread->addReply([
            'body' => request('body'),
            'user_id' => auth()->id(),
        ]);

        // Inspect the body of the reply for the username mentions
        preg_match_all('/\@([^\s\.]+)/',$reply->body,$matches);

        $names = $matches[1];

        // And then notify user
        foreach ($names as $name){
            $user = User::whereName($name)->first();

            if($user){
                $user->notify(new YouWereMentioned($reply));
            }
        }

        return $reply->load('owner');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Reply  $reply
     * @return \Illuminate\Http\Response
     */
    public function show(Reply $reply)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Reply  $reply
     * @return \Illuminate\Http\Response
     */
    public function edit(Reply $reply)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Reply  $reply
     * @return \Illuminate\Http\Response
     */
    public function update(Reply $reply)
    {
        $this->authorize('update',$reply);

        try{
            $this->validate(request(),['body' => 'required|spamfree']);

            $reply->update(request(['body']));
        }catch (\Exception $e){
            return response(
                'Sorry,your reply could not be saved at this time.',422
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Reply  $reply
     * @return \Illuminate\Http\Response
     */
    public function destroy(Reply $reply)
    {
        $this->authorize('update',$reply);

        $reply->delete();

        if (request()->expectsJson()){
            return response((['status' => 'Reply deleted']));
        }

        return back();
    }

    protected function validateReply()
    {
        $this->validate(request(),['body' => 'required']);

        resolve(Spam::class)->detect(request('body'));
    }
}
