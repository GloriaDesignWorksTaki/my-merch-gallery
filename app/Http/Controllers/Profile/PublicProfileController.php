<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class PublicProfileController extends Controller
{
    public function show(User $user): Response
    {
        $user->loadCount([
            'posts' => fn ($query) => $query->visibleOnFeed(),
        ]);

        return Inertia::render('Users/Show', [
            'profileUser' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'bio' => $user->bio,
                'avatar_path' => $user->avatar_path,
                'avatar_focus_x' => $user->avatar_focus_x,
                'avatar_focus_y' => $user->avatar_focus_y,
                'avatar_zoom' => $user->avatar_zoom,
                'posts_count' => $user->posts_count,
            ],
        ]);
    }
}
