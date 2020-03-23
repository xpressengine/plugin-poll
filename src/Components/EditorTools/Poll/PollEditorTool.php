<?php

namespace Xpressengine\Plugins\Poll\Components\EditorTools\Poll;

use App\Facades\XeFrontend;
use Xpressengine\Editor\AbstractTool;
use Symfony\Component\DomCrawler\Crawler;
use Xpressengine\Plugins\Poll\Models\Poll;
use Xpressengine\Plugins\Poll\Models\PollLog;
use Carbon\Carbon;
use Auth;

class PollEditorTool extends AbstractTool
{

    static public $init = false;

    public static function boot()
    {
    }

    /**
     * Initialize assets for the tool
     *
     * @return void
     */
    public function initAssets()
    {
        XeFrontend::html('ckeditor.xe_poll_editor_tool.load_url')->content("
        <script>
            (function() {
                var _url = {
                    popup: '".route('xe_poll::editor_tool.popup')."'
                };
                
                window.XePollToolURL = {
                    get: function (type) {
                        return _url[type];                 
                    }
                };
            })();
        </script>
        ")->load();

        XeFrontend::js([
            asset($this->getAssetsPath() . '/PollEditorTool.js')
        ])->load();
    }

    public function getIcon()
    {
        return asset($this->getAssetsPath() . '/images/icn_poll.png');
    }

    /**
     * Compile the raw content to be useful
     *
     * @param string $content content
     * @return string
     */
    public function compile($content)
    {
        $polls = $this->getData($content, '.__xe_poll', ['data-id', 'data-skin']);

        if (count($polls) > 0) {
            $content = '';
        }
        foreach ($polls as $key => $value) {
            $content .= $this->renderSkin($value['data-id'], $value['data-skin']);
        }

        return $content;
    }

    private function getAssetsPath()
    {
        $path = str_replace(base_path(), '', realpath(__DIR__ . '/assets'));
        $path = str_replace('/privates/', '/plugins/', $path);
        return $path;
    }

    public function getData($content, $selector, $attributes = [])
    {
        $attributes = !is_array($attributes) ? [$attributes] : $attributes;

        $crawler = $this->createCrawler($content);
        return $crawler->filter($selector)->each(function ($node, $i) use ($attributes) {
            $dom = $node->getNode(0);
            $data = [
                'html' => $dom->ownerDocument->saveHTML($dom),
                'inner' => $node->html(),
                'text' => $node->text(),
            ];

            foreach ($attributes as $attr) {
                $data[$attr] = $node->attr($attr);
            }

            return $data;
        });
    }

    public function createCrawler($content)
    {
        return new Crawler($content);
    }

    public function renderSkin($id, $skin)
    {
        $user = Auth::user();

        $poll = Poll::find($id);

        if ($skin == null) {
            $skin = 'default';
        }

        $path = app('xe.xe_poll.handler')->getSkinBladePath($skin);

        $javascriptInit = false;
        if (self::$init == false) {
            $javascriptInit = true;
            self::$init = true;
        }

        $current = Carbon::now();
        $isExpired = false;
        // check expired
        if ($current->gt($poll->expired_at)) {
            $isExpired = true;
        }

        $isJoined = false;
        $pollLog = null;
        if (Auth::check() === true) {
            $pollLog = PollLog::where('poll_id', $poll->id)->where('user_id', $user->getId())->first();
        }

        if ($pollLog != null) {
            $isJoined = true;
        }

        $view =  app('view')->make($path, [
            'poll' => $poll,
            'current_url' => url()->current(),
            'javascriptInit' => $javascriptInit,
            'isExpired' => $isExpired,
            'isJoined' => $isJoined,
        ])->render();
        return $view;
    }
}
