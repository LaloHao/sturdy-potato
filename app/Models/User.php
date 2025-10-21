<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'karma',
        'badge',
        'avatar',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected $appends = ['decisions_count', 'votes_count'];

    public function decisions(): HasMany
    {
        return $this->hasMany(Decision::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }
    
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function getDecisionsCountAttribute()
    {
        return $this->decisions()->count();
    }

    public function getVotesCountAttribute()
    {
        return $this->votes()->count();
    }

    public function updateBadge()
    {
        $decisionsCount = $this->decisions_count;
        $votesCount = $this->votes_count;

        if ($decisionsCount > $votesCount * 2) {
            $this->badge = 'Overthinker';
        } elseif ($votesCount > $decisionsCount * 2) {
            $this->badge = 'Consejero';
        } else {
            $this->badge = 'Decisivo';
        }

        $this->save();
    }
}
