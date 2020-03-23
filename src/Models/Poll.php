<?php
namespace Xpressengine\Plugins\Poll\Models;

use Illuminate\Database\Eloquent\Model;

class Poll extends Model
{
    protected $table = 'polls';

    protected $casts = [
        'poll_count' => 'int',
        'expired_at' => 'datetime',
    ];

    protected $fillable = [
        'user_id', 'target_id', 'check_count', 'poll_count', 'title', 'ipaddress', 'expired_at',
    ];

    public function user()
    {
        return $this->belongsTo('Xpressengine\User\Models\User', 'user_id');
    }

    public function items()
    {
        return $this->hasMany(PollItem::class, 'poll_id');
    }
}
