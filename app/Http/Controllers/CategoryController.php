<?php

namespace App\Http\Controllers;

use App\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    public function index () {
        $categories = Category::where('user_id', auth()->id())->get();

        return response()->json([
            'categories' => $categories
        ],Response::HTTP_OK);
    }

    public function store()
    {
        $data = request()->all();
        $categories = Category::where('user_id', auth()->id())->get();
        $index = 0;
        foreach ( $categories as $category) {
            $category->update(['name' => $data[$index]['name']]);
            $index++;
        }

        if ($categories){
            return response()->json([
                'success' => true,
                'message'=> "Successfully updated the Category name."
            ],Response::HTTP_OK);
        }else{
            return response()->json([
                'success' => false,
                'message'=> "Failed to update Category name"
            ],Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
