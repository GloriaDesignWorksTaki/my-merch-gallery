<?php
/**
 * ログインユーザーのプロフィール編集・退会
 * @package App\Http\Controllers
 */
namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
  public function edit(Request $request): Response
  {
    $user = $request->user();

    return Inertia::render('Profile/Edit', [
      'mustVerifyEmail' => $user instanceof MustVerifyEmail,
      'status' => session('status'),
      'email' => $user->email,
      'emailVerifiedAt' => $user->email_verified_at,
    ]);
  }

  public function update(ProfileUpdateRequest $request): RedirectResponse
  {
    $user = $request->user();
    $validated = $request->validated();

    $user->fill([
      'name' => $validated['name'],
      'email' => $validated['email'],
      'bio' => $validated['bio'] ?? null,
      'avatar_focus_x' => $validated['avatar_focus_x'] ?? $user->avatar_focus_x ?? 50,
      'avatar_focus_y' => $validated['avatar_focus_y'] ?? $user->avatar_focus_y ?? 50,
      'avatar_zoom' => $validated['avatar_zoom'] ?? $user->avatar_zoom ?? 1,
    ]);

    if (! empty($validated['remove_avatar']) && $user->avatar_path) {
      Storage::disk('public')->delete($user->avatar_path);
      $user->avatar_path = null;
      $user->avatar_focus_x = 50;
      $user->avatar_focus_y = 50;
      $user->avatar_zoom = 1;
    }

    if ($request->hasFile('avatar')) {
      if ($user->avatar_path) {
        Storage::disk('public')->delete($user->avatar_path);
      }

      $user->avatar_path = $request->file('avatar')->store('avatars', 'public');
    }

    if ($user->isDirty('email')) {
      $user->email_verified_at = null;
    }

    $user->save();

    return Redirect::route('profile.edit');
  }

  public function destroy(Request $request): RedirectResponse
  {
    $request->validate([
      'password' => ['required', 'current_password'],
    ]);

    $user = $request->user();

    Auth::logout();

    if ($user->avatar_path) {
      Storage::disk('public')->delete($user->avatar_path);
    }

    $user->delete();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return Redirect::to('/');
  }
}
