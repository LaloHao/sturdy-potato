<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\CommentNotification;
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

        // Get the authenticated user (commenter)
        $user = Auth::user();
        
        // Increment user's karma
        $user->karma += 5;
        $user->save();

        // Update user's badge based on new karma
        $user->updateBadge();

        // Notify the decision owner if they are not the one commenting
        $decisionOwner = $decision->user;
        if ($decisionOwner && $decisionOwner->id !== $user->id) {
            // Crear una notificación personalizada en nuestra tabla
            CommentNotification::create([
                'user_id' => $decisionOwner->id,
                'comment_id' => $comment->id,
                'decision_id' => $decision->id,
                'commenter_id' => $user->id,
                'comment_preview' => substr($comment->content, 0, 100) . (strlen($comment->content) > 100 ? '...' : ''),
                'read' => false,
            ]);
        }

        return response()->json($comment, 201);
    }

    /**
     * Update the specified comment in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Decision  $decision
     * @param  \App\Models\Comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Decision $decision, Comment $comment)
    {
        // Check if user is authorized to update this comment
        if ($comment->user_id != Auth::id()) {
            return response()->json(['message' => 'No estás autorizado para editar este comentario'], 403);
        }

        // Validate the request
        $request->validate([
            'content' => 'required|string|min:10|max:1000',
        ]);

        // Update the comment
        $comment->content = $request->content;
        $comment->save();

        // Return the updated comment
        $comment->load('user:id,name,email,avatar');
        return response()->json($comment);
    }

    /**
     * Remove the specified comment from storage.
     *
     * @param  \App\Models\Decision  $decision
     * @param  \App\Models\Comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function destroy(Decision $decision, Comment $comment)
    {
        // Check if user is authorized to delete this comment
        if ($comment->user_id != Auth::id()) {
            return response()->json(['message' => 'No estás autorizado para eliminar este comentario'], 403);
        }

        // Delete the comment
        $comment->delete();

        // Decrease user's karma (penalty for deleting content)
        $user = Auth::user();
        $user->karma = max(0, $user->karma - 2); // Ensure karma doesn't go below 0
        $user->save();

        // Update user's badge based on new karma
        $user->updateBadge();

        return response()->json(['message' => 'Comentario eliminado correctamente']);
    }
}
