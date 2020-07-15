<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $user = $request->user();
        return [
            'id' => $user->id,
            'avatar_id' => $user->avatar_id,
            'avatar_image' => $user->avatar_image,
            'name' => $user->name,
            'email' => $user->email,
            'thumb_up_playlist' => $user->thumb_up_playlist,
            'role_id' => $user->roles()->first()->id
        ];
    }
}
