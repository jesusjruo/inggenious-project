<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Follow;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class FollowController extends Controller
{
    public function followUser(User $user){
        
        if ($user->id == auth()->user()->id) {
            return back()->with('error' , 'You cannot follow yourself.');
        }

        $alreadyFollowing = Follow::where([['user_id' , '=' , auth()->user()->id] , ['followeduser' , '=' , $user->id]])->count();

        if ($alreadyFollowing) {
            return back()->with('error' , 'You already following this user.');
        }

        $newFollow = new Follow;
        $newFollow->user_id = auth()->user()->id;
        $newFollow->followeduser = $user->id;
        $newFollow->save();

        return back()->with('success' , 'User successfully followed.');
    }

    public function unfollowuser(User $user){

        Follow::where([['user_id' , '=' , auth()->user()->id] , ['followeduser' , '=' , $user->id]])->delete();
        return back()->with('success' , 'User successfully unfollowed');
    }
}
