<?php
namespace Xpressengine\Plugins\Poll\Controllers;

use App\Http\Controllers\Controller;
use XePresenter;
use Xpressengine\Http\Request;
use Xpressengine\Plugins\Poll\Handler;
use Xpressengine\Plugins\Poll\Models\Poll;
use Xpressengine\Plugins\Poll\Models\PollLog;
use Xpressengine\Plugins\Poll\Plugin;
use XeFrontend;
use Carbon\Carbon;
use Auth;
use Xpressengine\Support\Exceptions\AccessDeniedHttpException;

class UserController extends Controller
{
    public function toolPopup(Request $request)
    {
        if (Auth::check() === false) {
            throw new AccessDeniedHttpException;
        }

        $title = 'xe_poll::pollCreate';

        // set browser title
        XeFrontend::title(xe_trans($title));

        //header, footer 제거
        \XeTheme::selectBlankTheme();

        XeFrontend::css([
            asset('assets/vendor/bootstrap/css/bootstrap.min.css'),
            asset('assets/vendor/jqueryui/jquery-ui.min.css'),
        ])->load();

        XeFrontend::js([
            asset('assets/vendor/jqueryui/jquery-ui.min.js'),
//            Plugin::asset('src/Components/EditorTools/Poll/assets/PollEditorTool.js'),
        ])->appendTo('body')->load();

        $current = Carbon::now();
        $expiredAt = $current->addDay(7)->format('Y-m-d');

        XePresenter::widgetParsing(false);
        return XePresenter::make('xe_poll::views.user.tool_popup', [
            'title' => $title,
            'expiredAt' => $expiredAt,
            'routeStore' => route('xe_poll::editor_tool.store'),
        ]);
    }

    public function toolStore(Request $request, Handler $handler)
    {
        if (Auth::check() === false) {
            throw new AccessDeniedHttpException;
        }
        $user = Auth::user();

        \Log::info($request->all());

        \XeDB::beginTransaction();
        $params = [
            'user_id' => $user->getId(),
            'title' => $request->get('poll_title'),
            'expired_at' => $request->get('expired_at'),
            'ipaddress' => $request->ip(),
        ];
        $poll = $handler->storePoll($params);

        $survey_count = $request->get('survey_count');
        for ($i=0; $i<$survey_count; $i++) {
            $params = [
                'poll_id' => $poll->id,
                'title' => $request->get('survey_title_' . $i),
                'check_count' => $request->get('check_count_' . $i),
                'option_titles' => $request->get('option_title_' . $i)
            ];
            $handler->storePollItem($params);
        }
        \XeDB::commit();

        return XePresenter::makeApi(['type'=>'success','message'=>'저장했습니다.', 'id'=>$poll->id]);
    }

    public function toolJoin(Request $request, Handler $hander, $id)
    {
        if (Auth::check() === false) {
            return redirect()->back()->with(
                ['alert' => ['type' => 'danger', 'message' => '로그인이 필요한 서비스입니다. 로그인 후 이용해주세요.']]
            );
        }
        $user = Auth::user();

        $poll = Poll::find($id);

        $current = Carbon::now();
        // check expired
        if ($current->gt($poll->expired_at)) {
            return redirect()->back()->with(
                ['alert' => ['type' => 'danger', 'message' => '투표가 종료되었습니다.']]
            );
        }

        // check is joined
        $pollLog = PollLog::where('poll_id', $poll->id)->where('user_id', $user->getId())->first();
        if ($pollLog != null) {
            return redirect()->back()->with(
                ['alert' => ['type' => 'danger', 'message' => '이미 참여했습니다.']]
            );
        }

        $options_ids = [];
        foreach ($poll->items as $item) {
            $options = $request->get('option_'.$item->id);
            if (is_array($options) == false) {
                $options = [$options];
            }

            $options_ids = array_merge($options_ids, $options);
        }

        $params = [
            'user_id' => $user->getId(),
            'poll_id' => $poll->id,
            'ipaddress' => $request->ip(),
            'options_ids' => implode(',', $options_ids),
            'created_at' => $current,
        ];
        $pollLog = $hander->storeLog($params);

        $hander->increasePollCount($pollLog);

        return redirect($request->get('return_url'))->with(
            ['alert' => ['type' => 'success', 'message' => '투표 참여 감사합니다.']]
        );
    }

    public function create()
    {

    }

    public function store()
    {

    }

    public function edit()
    {

    }

    public function update()
    {

    }
}
