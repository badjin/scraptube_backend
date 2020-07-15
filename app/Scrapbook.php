<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Scrapbook extends Model
{
    protected $casts = [
        'tags' => 'array',
    ];

    protected $fillable = ['user_id', 'category', 'title', 'video_id', 'video_url', 'tags'];

    public function user() {
        return $this->belongsTo(User::class);
    }
}
