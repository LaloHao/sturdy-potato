<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommentNotification extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'comment_id',
        'decision_id',
        'commenter_id',
        'comment_preview',
        'read',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'read' => 'boolean',
    ];

    /**
     * Get the user that owns the notification.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the comment related to this notification.
     */
    public function comment(): BelongsTo
    {
        return $this->belongsTo(Comment::class);
    }

    /**
     * Get the decision related to this notification.
     */
    public function decision(): BelongsTo
    {
        return $this->belongsTo(Decision::class);
    }

    /**
     * Get the commenter user.
     */
    public function commenter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'commenter_id');
    }

    /**
     * Mark the notification as read.
     */
    public function markAsRead(): self
    {
        $this->read = true;
        $this->save();
        return $this;
    }

    /**
     * Mark the notification as unread.
     */
    public function markAsUnread(): self
    {
        $this->read = false;
        $this->save();
        return $this;
    }
}
