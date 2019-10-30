<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\User;
use Intervention\Image\Facades\Image;


class ProfileController extends Controller
{
    public function index(User $user)
    {
        $follows = (auth()->user()) ? auth()->user()->following->contains($user->id) : false ;

        $postscount = Cache::remember(
            'count.posts.' . $user->id,
            now()->addSeconds(60),
            function () use ($user) {
                return $user->posts->count();
            }
        );
        $followerscount = Cache::remember(
            'count.followers.' . $user->id,
            now()->addSeconds(60),
            function () use ($user) {
                return $user->profile->followers->count();
            }
        );
        $followingcount = Cache::remember(
            'count.following.' . $user->id,
            now()->addSeconds(60),
            function () use ($user) {
                return $user->following->count();
            }
        );

       return view('profiles.index', compact('user' , 'follows' , 'postscount', 'followerscount', 'followingcount'));
    }

    public function edit(User $user){
        $this->authorize('update', $user->profile);
        return view('profiles.edit', compact('user'));
    }

    public function update(User $user){
        $data = request()->validate([
            'title' => 'required',
            'description' => 'required',
            'url' => 'url',
            'image' => '',
        ]);
        
        if (request('image')){

            $imagePath = request('image')->store('uploads/profileimage','public');
        
            $image = Image::make(public_path("storage/{$imagePath}"))->fit(500, 500);
            $image->save();

            $imageArray = ['image' => $imagePath];
        }

        auth()->user()->profile->update(array_merge(
            $data,
            $imageArray ?? []
        ));

        return redirect("profile/{$user->id}");
    }
}
