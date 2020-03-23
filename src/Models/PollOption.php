<?php
namespace Xpressengine\Plugins\Poll\Models;

use Illuminate\Database\Eloquent\Model;

class PollOption extends Model
{
    protected $table = 'poll_options';

    public $timestamps = false;

    protected $casts = [
        'poll_count' => 'int',
    ];

    protected $fillable = [
        'poll_item_id', 'poll_count', 'title',
    ];
}
