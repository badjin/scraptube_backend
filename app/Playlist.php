<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Playlist extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'thumb_up_count'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function songList(){
        return $this->hasMany(SongList::class);
    }
}
