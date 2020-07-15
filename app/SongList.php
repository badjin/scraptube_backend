<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SongList extends Model
{
    protected $fillable = [
        'playlist_id',
        'title',
        'video_id',
        'video_url'
    ];

    public function playlist() {
        return $this->belongsTo(Playlist::class);
    }
}
