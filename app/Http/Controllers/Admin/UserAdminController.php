<?php
/**
 * 管理画面：ユーザー一覧・BAN・ロール
 * @package App\Http\Controllers\Admin
 */
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class UserAdminController extends Controller
{
  public function index(Request $request): Response
  {
  $this->authorize('viewAny', User::class);

  $q = trim((string) $request->string('q')->toString());

  $query = User::query()
  ->select(['id', 'name', 'username', 'email', 'role', 'banned_at', 'created_at'])
  ->orderByDesc('id');

  if ($q !== '') {
  $query->where(function ($sub) use ($q) {
    $sub->where('name', 'like', '%'.$q.'%')
    ->orWhere('username', 'like', '%'.$q.'%')
    ->orWhere('email', 'like', '%'.$q.'%');
  });
  }

  return Inertia::render('Admin/Users', [
  'users' => $query->paginate(24)->withQueryString(),
  'filters' => ['q' => $q],
  ]);
  }

  public function ban(Request $request, User $target): RedirectResponse
  {
  $this->authorize('ban', $target);

  $target->forceFill(['banned_at' => now()])->save();

  return back()->with('status', 'admin-user-banned');
  }

  public function unban(Request $request, User $target): RedirectResponse
  {
  $this->authorize('unban', $target);

  $target->forceFill(['banned_at' => null])->save();

  return back()->with('status', 'admin-user-unbanned');
  }

  public function updateRole(Request $request, User $target): RedirectResponse
  {
  $this->authorize('updateRole', $target);

  $data = $request->validate([
  'role' => ['required', Rule::in(['user', 'admin', 'owner'])],
  ]);

  $target->forceFill(['role' => $data['role']])->save();

  return back()->with('status', 'admin-user-role-updated');
  }
}
