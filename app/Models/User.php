<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
  'name',
  'username',
  'email',
  'password',
  'bio',
  'avatar_path',
  'role',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
  /** @use HasFactory<UserFactory> */
  use HasFactory, Notifiable;

  /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
  protected function casts(): array
  {
    return [
      'email_verified_at' => 'datetime',
      'password' => 'hashed',
    ];
  }

  public function createdBands()
  {
    return $this->hasMany(Band::class, 'created_by');
  }

  public function createdMerchItems()
  {
    return $this->hasMany(MerchItem::class, 'created_by');
  }

  public function posts()
  {
    return $this->hasMany(Post::class);
  }

  public function comments()
  {
    return $this->hasMany(Comment::class);
  }

  public function postLikes()
  {
    return $this->hasMany(PostLike::class);
  }
}
