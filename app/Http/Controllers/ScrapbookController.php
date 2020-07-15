<?php

namespace App\Http\Controllers;

use App\Scrapbook;
use Illuminate\Http\Response;

class ScrapbookController extends Controller
{
    public function index()
    {
        $videos = auth()->user()->scrapbooks()
            ->where('category', request(['category']))
            ->latest()
            ->paginate(24);

        return response()->json([
            'videos' => $videos
        ], Response::HTTP_OK);
    }

    public function store()
    {
        request()->validate([
            'title' => 'required | min:3',
            'video_id' => 'required',
            'video_url' => 'required | url'
        ]);

        $value = request(['title', 'video_id', 'video_url', 'category', 'tags']);
        $value['user_id'] = auth()->id();

        $scrapbook = Scrapbook::create($value);

        if (!$scrapbook) {
            return response()->json([
                'success' => false,
                'message' => "There was an error adding the Scrapbook.",
            ],Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            'success' => true,
            'message'=> "The Scrapbook has been successfully added.",
            'scrapbook' => $scrapbook
        ],Response::HTTP_OK);
    }

    public function update()
    {
        request()->validate([
            'title' => 'required',
            'video_url' => 'required',
            'video_id' => 'required',
        ]);

        if (request()->id == null)
            $scrapbook = Scrapbook::all()->where('video_id', request()->video_id)->first();
        else
            $scrapbook = Scrapbook::all()->find(request()->id);

        if ($scrapbook->update(request()->all())){
            return response()->json([
                'success' => true,
                'message'=> "The Scrapbook has been successfully updated."
            ],Response::HTTP_OK);
        }else{
            return response()->json([
                'success' => false,
                'message'=> "There was an error updating the Scrapbook."
            ],Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy()
    {

        $scrapbook = Scrapbook::all()->find(request()->id);

        if ($scrapbook->delete()) {
            return response()->json([
                'message' => 'The Scrapbook has been successfully deleted.',
            ], Response::HTTP_OK);
        } else {
            return response()->json([
                'message' => 'There was an error deleting the Scrapbook.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}
