<?php

namespace NexusPlugin\DoubleColorBall\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Period Model
 * 
 * Represents a lottery draw period.
 * 
 * @property int $id
 * @property string $period_code Period code (YYYYMMDDNN format)
 * @property int $status Status: 0=Open, 1=Closed, 2=Drawn
 * @property array|null $red_balls Winning red balls
 * @property array|null $blue_balls Winning blue balls
 * @property string|null $block_hash Bitcoin block hash
 * @property int|null $block_height Bitcoin block height
 * @property float $prize_pool Total prize pool
 * @property array|null $win_details Winning statistics
 * @property \Carbon\Carbon|null $opened_at Draw time
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 */
class Period extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'nexus_plugin_dcb_periods';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'period_code',
        'status',
        'red_balls',
        'blue_balls',
        'block_hash',
        'block_height',
        'prize_pool',
        'win_details',
        'opened_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'red_balls' => 'array',
        'blue_balls' => 'array',
        'prize_pool' => 'decimal:2',
        'win_details' => 'array',
        'opened_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Status constants
    const STATUS_OPEN = 0;      // Tickets can be purchased
    const STATUS_CLOSED = 1;    // Ticket sales closed, waiting for draw
    const STATUS_DRAWN = 2;     // Draw completed

    /**
     * Get all tickets for this period.
     */
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'period_id');
    }

    /**
     * Check if the period is open for ticket purchases.
     */
    public function isOpen(): bool
    {
        return $this->status === self::STATUS_OPEN;
    }

    /**
     * Check if the period is closed.
     */
    public function isClosed(): bool
    {
        return $this->status === self::STATUS_CLOSED;
    }

    /**
     * Check if the period has been drawn.
     */
    public function isDrawn(): bool
    {
        return $this->status === self::STATUS_DRAWN;
    }

    /**
     * Get the status text.
     */
    public function getStatusTextAttribute(): string
    {
        return match($this->status) {
            self::STATUS_OPEN => nexus_trans('dcb::dcb.status.open'),
            self::STATUS_CLOSED => nexus_trans('dcb::dcb.status.closed'),
            self::STATUS_DRAWN => nexus_trans('dcb::dcb.status.drawn'),
            default => nexus_trans('dcb::dcb.status.unknown'),
        };
    }

    /**
     * Get formatted winning numbers.
     */
    public function getFormattedWinningNumbersAttribute(): string
    {
        if (!$this->isDrawn()) {
            return '-';
        }

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
     * Scope to get current open period.
     */
    public function scopeCurrentOpen($query)
    {
        return $query->where('status', self::STATUS_OPEN)
            ->orderBy('created_at', 'desc')
            ->first();
    }

    /**
     * Scope to get periods ready for drawing.
     */
    public function scopeReadyForDraw($query)
    {
        return $query->where('status', self::STATUS_CLOSED);
    }
}
