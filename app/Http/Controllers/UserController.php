<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Follow;
use Illuminate\Http\Request;
use App\Events\OurExampleEvent;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\View;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Imagick\Driver;

class UserController extends Controller
{
    //

    public function showCorrectHomepage() {
        if (auth()->check()) {
            return view('homepage-feed' , ['posts' => auth()->user()->feedPosts()->latest()->paginate(5)]);
        } else {
            return view('homepage');
        }
    }

    public function register(Request $request) {
        $incomingFields = $request->validate([
            'username' => ['required' , 'min:3' , 'max:20' , Rule::unique('users' , 'username')],
            'email' => ['required', 'email' , Rule::unique('users' , 'email')],
            'password' => ['required' , 'min:8' , 'confirmed']
        ]);
        $user = User::create($incomingFields);
        auth()->login($user);
        return redirect('/')->with('success' , 'Thank you for creating an account.');
    }

    public function login(Request $request) {
        $incomingFields = $request->validate([
            'loginusername' => 'required',
            'loginpassword' => 'required'
        ]);

        if (auth()->attempt(['username' => $incomingFields['loginusername'] , 'password' => $incomingFields['loginpassword']])) {
            $request->session()->regenerate();
            event(new OurExampleEvent(['username' => auth()->user()->username , 'action' => 'login']));
            return redirect('/')->with('success' , 'You have succesfully logged in.');
        } else {
            return redirect('/')->with('error' , 'Invalid login.');
        }
    }

    public function logout() {
        event(new OurExampleEvent(['username' => auth()->user()->username , 'action' => 'logout']));
        auth()->logout();
        return redirect('/')->with('success' , 'You are now logged out.');
    }

    private function getSharedData($user) {
        $currentlyFollowing = 0;

        if (auth()->check()) {
             $currentlyFollowing = Follow::where([['user_id' , '=' , auth()->user()->id] , ['followeduser' , '=' , $user->id]])->count();
        }

        View::share( 'sharedData' , ['avatar' => $user->avatar , 'username' => $user->username , 'postCount' => $user->posts()->count(), 'currentlyFollowing' => $currentlyFollowing , 'followerCount' =>$user->followers()->count() , 'followingCount' =>$user->following()->count()]);
    }

    public function profile(User $user) {
        $this->getSharedData($user);
        return view('profile-posts' , ['posts' => $user->posts()->latest()->get()]);
    }

    public function profileFollowers(User $user) {
        $this->getSharedData($user);
        return view('profile-followers' , ['followers' => $user->followers()->latest()->get()]);
    
    }

    public function profileFollowing(User $user) {
        $this->getSharedData($user);
        return view('profile-following' , ['following' => $user->following()->latest()->get()]);
    
    }



    public function showAvatarForm() {
        return view('edit-avatar');
    }

    public function storeAvatar(Request $request) {
        $request->validate([
            'avatar' => 'required|image|max:6000'
        ]);

        $user = auth()->user();

        $filename = $user->id . '-' . uniqid() . '.jpg';

        $imgData = Image::make($request->file('avatar'))->fit(120)->encode('jpg');
        Storage::put('public/avatars/' . $filename , $imgData);

        $oldAvatar = $user->avatar;

        $user->avatar = $filename;
        $user->save();

        if ($oldAvatar != "/fallback-avatar.jpg") {
            Storage::delete(str_replace("/storage/" , "public/" , $oldAvatar));
        } 

        return back()->with('success' , 'Avatar updated succesfully.');
    }
}
