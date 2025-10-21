<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Decision extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'context',
        'type',
        'is_anonymous',
        'status',
        'expires_at',
        'final_option_id',
    ];

    protected $casts = [
        'is_anonymous' => 'boolean',
        'expires_at' => 'datetime',
    ];

    protected $appends = ['time_remaining', 'total_votes', 'is_expired'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(Option::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }
    
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function finalOption(): BelongsTo
    {
        return $this->belongsTo(Option::class, 'final_option_id');
    }

    public function getTimeRemainingAttribute()
    {
        if ($this->expires_at) {
            return Carbon::now()->diffForHumans($this->expires_at, true);
        }
        return null;
    }

    public function getTotalVotesAttribute()
    {
        return $this->votes()->count();
    }

    public function getIsExpiredAttribute()
    {
        return $this->expires_at && Carbon::now()->isAfter($this->expires_at);
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'open')
                    ->where('expires_at', '>', Carbon::now());
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', Carbon::now())
                    ->orWhere('status', 'expired');
    }

    public function checkAndUpdateExpiredStatus()
    {
        if ($this->is_expired && $this->status === 'open') {
            $this->update(['status' => 'expired']);
        }
    }

    public function hasUserVoted(User $user)
    {
        return $this->votes()->where('user_id', $user->id)->exists();
    }

    public function getUserVote(User $user)
    {
        return $this->votes()->where('user_id', $user->id)->first();
    }
}