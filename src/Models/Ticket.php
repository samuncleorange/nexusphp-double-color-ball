<?php

namespace NexusPlugin\DoubleColorBall\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

/**
 * Ticket Model
 * 
 * Represents a lottery ticket purchased by a user.
 * 
 * @property int $id
 * @property int $period_id
 * @property int $user_id
 * @property array $red_balls Selected red balls
 * @property array $blue_balls Selected blue balls
 * @property float $cost Ticket cost in magic points
 * @property int $win_level Winning level (0 = no win)
 * @property float $win_bonus Prize amount
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 */
class Ticket extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'nexus_plugin_dcb_tickets';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'period_id',
        'user_id',
        'red_balls',
        'blue_balls',
        'cost',
        'win_level',
        'win_bonus',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'red_balls' => 'array',
        'blue_balls' => 'array',
        'cost' => 'decimal:2',
        'win_bonus' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the period this ticket belongs to.
     */
    public function period(): BelongsTo
    {
        return $this->belongsTo(Period::class, 'period_id');
    }

    /**
     * Get the user who purchased this ticket.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Check if this ticket is a winner.
     */
    public function isWinner(): bool
    {
        return $this->win_level > 0;
    }

    /**
     * Get formatted ticket numbers.
     */
    public function getFormattedNumbersAttribute(): string
    {
        $red = implode(', ', $this->red_balls ?? []);
        $blue = implode(', ', $this->blue_balls ?? []);

        return sprintf(
            '%s: %s | %s: %s',
            nexus_trans('dcb::dcb.labels.red_balls'),
            $red,
            nexus_trans('dcb::dcb.labels.blue_balls'),
            $blue
        );
    }

    /**
     * Get win level text.
     */
    public function getWinLevelTextAttribute(): string
    {
        if ($this->win_level === 0) {
            return nexus_trans('dcb::dcb.win_level.no_win');
        }

        return nexus_trans('dcb::dcb.win_level.level_' . $this->win_level);
    }

    /**
     * Scope to get tickets for a specific user.
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to get winning tickets.
     */
    public function scopeWinners($query)
    {
        return $query->where('win_level', '>', 0);
    }

    /**
     * Scope to get tickets for a specific period.
     */
    public function scopeForPeriod($query, int $periodId)
    {
        return $query->where('period_id', $periodId);
    }
}
