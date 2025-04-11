<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function submit_review(Request $request){
        $request->validate([
            'text' => ['required'],
        ]);

        $review = Review::create([
            'comment' => $request->text,
            'role'=> $request->role,
            'rating' => $request->rating,
            'user_id' => $request->user_id,
            'product_id' => $request->product_id
        ]);

        
        return back();
    }
}
