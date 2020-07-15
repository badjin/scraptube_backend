<?php

namespace App;

use App\Notifications\PasswordResetNotification;
use App\Notifications\VerifyNotification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
        'name', 'avatar_id', 'email', 'password', 'avatar_image', 'thumb_up_playlist'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'thumb_up_playlist' => 'array'
    ];

    public function roles(){
        return $this->belongsToMany('App\Role');
    }

    public function hasAnyRoles($roles){
        if ($this->roles()->whereIn('name', $roles)->first()){
            return true;
        }
        return false;
    }

    public function hasRole($role){
        if ($this->roles()->where('name', $role)->first()){
            return true;
        }
        return false;
    }

    public function playlists(){
        return $this->hasMany(Playlist::class);
    }

    public function scrapbooks(){
        return $this->hasMany(Scrapbook::class);
    }

    public function categories(){
        return $this->hasMany(Category::class);
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new PasswordResetNotification($token));
    }

    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyNotification());
    }

}
