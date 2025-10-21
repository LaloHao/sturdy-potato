<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Decision;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CommentController extends Controller
{
    /**
     * Display a listing of the comments for a specific decision.
     *
     * @param  \App\Models\Decision  $decision
     * @return \Illuminate\Http\Response
     */
    public function index(Decision $decision)
    {
        // Get comments for the decision, ordered by most recent first, with user data
        $comments = $decision->comments()
            ->with('user:id,name,email,avatar')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
            
        return response()->json($comments);
    }

    /**
     * Store a newly created comment in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Decision  $decision
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Decision $decision)
    {
        // Validate the request
        $request->validate([
            'content' => 'required|string|min:10|max:1000',
        ]);

        // Create the comment
        $comment = $decision->comments()->create([
            'user_id' => Auth::id(),
            'content' => $request->content,
        ]);

        // Load the user relation for the response
        $comment->load('user:id,name,email,avatar');

        // Increment user's karma
        $user = Auth::user();
        $user->karma += 5;
        $user->save();

        // Update user's badge based on new karma
        $user->updateBadge();

        return response()->json($comment, 201);
    }
}
