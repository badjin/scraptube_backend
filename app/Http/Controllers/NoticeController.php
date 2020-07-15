<?php

namespace App\Http\Controllers;

use App\Notice;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class NoticeController extends Controller
{
    public function noticeSave() {
        $notice = request()->noticeImage;

        if (!Str::startsWith($notice, 'data:image')) return $notice;
        $noticeName = null;

        $name = time().'.' . explode('/', explode(':', substr($notice, 0, strpos($notice, ';')))[1])[1];
        Image::make($notice)->save(public_path('/images/notice/').$name);
        $noticeName = 'images/notice/'.$name;

        return url($noticeName);
    }

    public function index()
    {
        $notices = Notice::latest()->get();

        return response()->json([
            'notices' => $notices
        ], Response::HTTP_OK);
    }

    public function store(Request $request)
    {
        if (Gate::denies('edit-content')){
            return response()->json([],Response::HTTP_UNAUTHORIZED);
        }

        request()->validate([
            'noticeImage' => 'required'
        ]);

        $noticeName = $this->noticeSave();

        $notice = Notice::create(['notice_image' => $noticeName]);

        if (!$notice) {
            return response()->json([
                'success' => false,
                'message' => "There was an error storing the image file.",
            ],Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            'success' => true,
            'message'=> "The image file has been successfully stored.",
            'notice' => $notice
        ],Response::HTTP_OK);
    }

    public function destroy(Notice $notice)
    {
        if (Gate::denies('edit-content')){
            return response()->json([],Response::HTTP_UNAUTHORIZED);
        }

        $noticeName = request()->noticeImage;
        $notice = Notice::where('notice_image', $noticeName)->first();

        if ($noticeName){
            $name = basename($noticeName);
            File::delete(public_path('/images/notice/').$name);
        }

        if ($notice->delete()){
            return response()->json([
                'message' => 'The image file has been successfully deleted.'
            ],Response::HTTP_OK);
        }else{
            return response()->json([
                'message' => 'There was an error deleting the image file.',
            ],Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
