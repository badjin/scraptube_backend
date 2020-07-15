<?php

namespace App\Http\Controllers;

use App\TagItem;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class TagItemsController extends Controller
{
    public function index()
    {
        $num = request(['category']);
        $category = auth()->user()->categories()->get();
        $tags = $category[$num['category']-1]->tags()->get();

        return response()->json([
            'tags' => $tags
        ],Response::HTTP_OK);
    }

    public function store()
    {
        $data = request()->all();
        $category = auth()->user()->categories()->get();

        $value = request(['tags']);
        $value['category_id'] = $category[$data['category']-1]->id;

        $tags = $category[$data['category']-1]->tags()->get();

        if ($tags->count() > 0) {
            $affected = DB::table('tag_items')
                ->where('category_id', $value['category_id'])
                ->update(['tags' => $data['tags']]);
            if ($affected){
                return response()->json([
                    'success' => true,
                    'message'=> "Successfully updated the tag items."
                ],Response::HTTP_OK);
            }else{
                return response()->json([
                    'success' => false,
                    'message'=> "Failed to update the tag items."
                ],Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        $tags = TagItem::create($value);

        if (!$tags) {
            return response()->json([
                'success' => false,
                'message' => "There was an error adding the tag name.",
            ],Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            'success' => true,
            'message'=> "The tag name has been successfully added.",
            'tag' => $tags
        ],Response::HTTP_OK);
    }
}
