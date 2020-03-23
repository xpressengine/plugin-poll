<?php
namespace Xpressengine\Plugins\Poll\Models;

use Illuminate\Database\Eloquent\Model;

class PollItem extends Model
{
    protected $table = 'poll_items';

    public $timestamps = false;

    protected $casts = [
        'poll_count' => 'int',
    ];

    protected $fillable = [
        'poll_id', 'poll_count', 'title',
    ];

    public function options()
    {
        return $this->hasMany(PollOption::class, 'poll_item_id');
    }
}
