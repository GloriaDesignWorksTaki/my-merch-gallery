<?php
/**
 * ユーザーアカウントのモデル定義
 * @package App\Models
 */
namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

#[Fillable([
  'name',
  'username',
  'email',
  'password',
  'bio',
  'avatar_path',
  'avatar_focus_x',
  'avatar_focus_y',
  'avatar_zoom',
  'role',
  'theme',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
  /** @use HasFactory<UserFactory> */
  use HasFactory, Notifiable;

  protected $appends = [
    'avatar_url',
  ];

  protected function casts(): array
  {
    return [
      'email_verified_at' => 'datetime',
      'password' => 'hashed',
      'avatar_focus_x' => 'integer',
      'avatar_focus_y' => 'integer',
      'avatar_zoom' => 'float',
      'banned_at' => 'datetime',
    ];
  }
  public function isOwner(): bool
  {
    return $this->role === 'owner';
  }
  public function isAdmin(): bool
  {
    return $this->role === 'admin';
  }
  public function isStaff(): bool
  {
    return $this->isOwner() || $this->isAdmin();
  }
  public function isBanned(): bool
  {
    return $this->banned_at !== null;
  }
  public function createdBands()
  {
    return $this->hasMany(Band::class, 'created_by');
  }
  public function createdMerchItems()
  {
    return $this->hasMany(MerchItem::class, 'created_by');
  }
  public function merchItemComments()
  {
    return $this->hasMany(MerchItemComment::class);
  }
  public function merchItemLikes()
  {
    return $this->hasMany(MerchItemLike::class);
  }
  public function bandLikes()
  {
    return $this->hasMany(BandLike::class);
  }

  protected function avatarUrl(): Attribute
  {
    return Attribute::get(function (): ?string {
      if ($this->avatar_path === null || $this->avatar_path === '') {
        return null;
      }

      /** @var FilesystemAdapter $disk */
      $disk = Storage::disk('uploads');

      return $disk->url($this->avatar_path);
    });
  }
}
