<?php
namespace Xpressengine\Plugins\Poll\Models;

use Illuminate\Database\Eloquent\Model;

class PollLog extends Model
{
    protected $table = 'poll_logs';

    protected $casts = [
        'poll_count' => 'int',
        'created_at' => 'datetime',
    ];

    protected $fillable = [
        'poll_id', 'user_id', 'ipaddress', 'created_at', 'options_ids',
    ];

    /**
     * @var bool
     */
    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo('Xpressengine\User\Models\User', 'user_id');
    }
}
