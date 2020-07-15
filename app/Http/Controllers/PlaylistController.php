<?php

namespace App\Http\Controllers;

use App\Playlist;
use App\SongList;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class PlaylistController extends Controller
{
    public function index()
    {
        $playlists = request()->all();
        if ($playlists['table'] == 'id') {
            $playlists = DB::table('playlists')
                ->join('users', 'playlists.user_id', '=', 'users.id')
                ->select('playlists.*', 'users.avatar_id', 'users.avatar_image', 'users.name')
                ->latest()->paginate(10);
        } else {
            $playlists = DB::table('playlists')
                ->join('users', 'playlists.user_id', '=', 'users.id')
                ->select('playlists.*', 'users.avatar_id', 'users.avatar_image', 'users.name')
                ->where($playlists['table'], 'LIKE', '%'.$playlists['keyword'].'%')
                ->latest()->paginate(10);
        }

        foreach ($playlists as $playlist) {
            $playlist->song_list = SongList::where('playlist_id', $playlist->id)->get();
        }

        return response()->json([
            'playlists' => $playlists,
        ],Response::HTTP_OK);
    }

    public function store()
    {
        request()->validate([
            'title' => 'required | min:3',
            'song_list' => 'required'
        ]);

        $user = auth()->user();

        $value = request()->all();
        $pList = request(['title', 'thumb_up_count']);
        $pList['user_id'] = $user['id'];

        $playlist = Playlist::create($pList);

        if (!$playlist) {
            return response()->json([
                'success' => false,
                'message' => "There was an error storing the playlist.",
            ],Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $songList = $value['song_list'];
        foreach ($songList as $item) {
            $item['playlist_id'] = $playlist->id;
            $sList = SongList::create($item);
            if (!$sList) {
                return response()->json([
                    'success' => false,
                    'message' => "There was an error storing the playlist.",
                ],Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        return response()->json([
            'success' => true,
            'message'=> "The playlist has been successfully stored."
        ],Response::HTTP_OK);
    }

    public function update()
    {
        $user = auth()->user();
        if ($user['id'] != request()->user_id) {
            if (Gate::denies('edit-content')){
                return response()->json([],Response::HTTP_UNAUTHORIZED);
            }
        }

        request()->validate([
            'title' => 'required',
            'song_list' => 'required'
        ]);

        $pList = request(['title', 'thumb_up_count']);
        $sList = request(['song_list']);
        $playlist = Playlist::all()->find(request()->id);
        $songList = $playlist->songList()->get();
        foreach ($songList as $song) {
            $song->delete();
        }
        foreach ($sList['song_list'] as $song) {
            $song['playlist_id'] = $playlist->id;
            SongList::create($song);
        }

        if ($playlist->update($pList)){
            return response()->json([
                'success' => true,
                'message'=> "The playlist has been successfully updated."
            ],Response::HTTP_OK);
        }else{
            return response()->json([
                'success' => false,
                'message'=> "There was an error updating the playlist."
            ],Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function updateThumbUp()
    {
        $user = auth()->user();
        if ($user['id'] == request()->user_id) {
            return response()->json([
                'success' => false,
                'message'=> "Not applicable to yourself."
            ],Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $playlist = Playlist::all()->find(request()->id);

        if ($playlist->update(request()->all())){
            return response()->json([
                'success' => true,
                'message'=> ""
            ],Response::HTTP_OK);
        }else{
            return response()->json([
                'success' => false,
                'message'=> "There was an error updating the playlist."
            ],Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy()
    {
        $user = auth()->user();
        if ($user['id'] != request()->user_id) {
            if (Gate::denies('edit-content')){
                return response()->json([],Response::HTTP_UNAUTHORIZED);
            }
        }

        $playlist = Playlist::all()->find(request()->id);

        if ($playlist->delete()) {
            return response()->json([
                'message' => 'The playlist has been successfully deleted.',
            ], Response::HTTP_OK);
        } else {
            return response()->json([
                'message' => 'There was an error deleting the playlist.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
