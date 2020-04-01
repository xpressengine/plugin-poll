<?php
namespace Xpressengine\Plugins\Poll;


use Xpressengine\Plugins\Poll\Models\Poll;
use Xpressengine\Plugins\Poll\Models\PollItem;
use Xpressengine\Plugins\Poll\Models\PollOption;
use Xpressengine\Plugins\Poll\Models\PollLog;

class Handler
{
    public function storePoll($args)
    {
        $item = new Poll();
        $item->user_id = $args['user_id'];
        $item->target_id = '';
        $item->poll_count = 0;
        $item->title = $args['title'];
        $item->expired_at = $args['expired_at'];
        $item->ipaddress = $args['ipaddress'];
        $item->save();

        return $item;
    }

    public function storePollItem($args)
    {
        $item = new PollItem();
        $item->poll_id = $args['poll_id'];
        $item->title = $args['title'];
        $item->check_count = $args['check_count'];
        $item->poll_count = 0;
        $item->save();

        foreach ($args['option_titles'] as $title) {
            $params = [
                'poll_item_id' => $item->id,
                'title' => $title,
            ];

            $optionItem = $this->storePollOption($params);
        }

        return $item;
    }

    public function storePollOption($args)
    {
        $item = new PollOption();
        $item->poll_item_id = $args['poll_item_id'];
        $item->title = $args['title'];
        $item->poll_count = 0;
        $item->save();

        return $item;
    }

    public function bind(array $ids, $targetId, $targetType)
    {
        if (count($ids) > 0) {
            Poll::whereIn('id', $ids)->update([
                'target_id' => $targetId,
                'target_type' => $targetType,
            ]);
        }
    }

    public function storeLog($args)
    {
        $item = new PollLog();
        $item->poll_id = $args['poll_id'];
        $item->user_id = $args['user_id'];
        $item->ipaddress = $args['ipaddress'];
        $item->options_ids = $args['options_ids'];
        $item->created_at = $args['created_at'];
        $item->save();

        return $item;
    }

    public function increasePollCount($pollLog)
    {
        Poll::where('id', $pollLog->poll_id)->increment('poll_count', 1);
        PollItem::where('poll_id', $pollLog->poll_id)->increment('poll_count', 1);
        PollOption::whereIn('id', explode(',', $pollLog->options_ids))->increment('poll_count', 1);
    }

    public function getSkinBlades()
    {
        $skins = [
            'default' => 'xe_poll::views.user.default',
        ];

        return $skins;
    }

    public function getSkinBladePath($skin = 'default')
    {
        $skins = $this->getSkinBlades();

        return $skins[$skin];
    }
}
