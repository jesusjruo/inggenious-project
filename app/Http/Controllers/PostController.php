<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Jobs\SendNewPostEmail;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;

class PostController extends Controller
{
    public function showCreateForm() {
        return view('create-post');
    }

    public function createPost(Request $request) {
        $incomingFields = $request->validate([
            'title' => 'required',
            'body' => 'required'
        ]);

        $incomingFields['title'] = strip_tags($incomingFields['title']);
        $incomingFields['body'] = strip_tags($incomingFields['body']);
        $incomingFields['user_id'] = auth()->id();

        $newPost = Post::create($incomingFields);

        dispatch(new SendNewPostEmail(['sendTo' => auth()->user()->email , 'name' => auth()->user()->username , 'title' => $newPost->title]));

        return redirect("/post/{$newPost->id}")->with('success', 'New post successfully created.');
    }

    public function createPostApi(Request $request) {
        $incomingFields = $request->validate([
            'title' => 'required',
            'body' => 'required'
        ]);

        $incomingFields['title'] = strip_tags($incomingFields['title']);
        $incomingFields['body'] = strip_tags($incomingFields['body']);
        $incomingFields['user_id'] = auth()->id();

        $newPost = Post::create($incomingFields);

        dispatch(new SendNewPostEmail(['sendTo' => auth()->user()->email , 'name' => auth()->user()->username , 'title' => $newPost->title]));

        return $newPost->id;
    }

    public function viewSinglePost(Post $post) {
        $post['body'] = strip_tags(Str::markdown($post->body) , '<p><h1><h2><h3><ul><ol><li><strong><br>');
        return view('single-post' , ['post' => $post]);
    }

    public function showEditForm(Post $post) {
        return view('edit-post' , ['post' => $post]);
    }

    public function updatePost(Request $request , Post $post) {
        $incomingFields = $request->validate([
            'title' => 'required',
            'body' => 'required'
        ]);

        $incomingFields['title'] = strip_tags($incomingFields['title']);
        $incomingFields['body'] = strip_tags($incomingFields['body']);

        $post->update($incomingFields);

        return redirect("/post/{$post->id}")->with('success', 'Your post was successfully updated.');
    }

    public function deletePost(Post $post) {
        $post->delete(); 

        return redirect('/profile/' . auth()->user()->username)->with('success' , 'Post successfully deleted.');
    }

    public function deletePostApi(Post $post) {
        $post->delete(); 

        return 'True';
    }

    public function search ($term) {
        $posts = Post::search($term)->get();
        $posts->load('user:id,username,avatar');
        return $posts;
    }
}
