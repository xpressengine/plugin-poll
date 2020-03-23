<?php
namespace Xpressengine\Plugins\Poll\Controllers;

use App\Http\Controllers\Controller;
use XePresenter;
use Xpressengine\Http\Request;
use Xpressengine\Plugins\Poll\Models\Poll;
use Carbon\Carbon;

class SettingController extends Controller
{
    public function index(Request $request)
    {
        $query = Poll::query();

        $current = Carbon::now();
        //기간 검색
        if ($endDate = $request->get('end_date', $current->format('Y-m-d'))) {
            $query = $query->where('created_at', '<=', $endDate . ' 23:59:59');
        }
        if ($startDate = $request->get('start_date', $current->subDay(7)->format('Y-m-d'))) {
            $query = $query->where('created_at', '>=', $startDate . ' 00:00:00');
        }

        if ($userEmail = $request->get('user_email')) {
            $writers = \XeUser::where(
                'email',
                'like',
                '%' . $userEmail . '%'
            )->selectRaw('id')->get();

            $writerIds = [];
            foreach ($writers as $writer) {
                $writerIds[] = $writer['id'];
            }
            $query = $query->whereIn('user_id', $writerIds);
        }

        $items = $query->orderBy('created_at', 'desc')->paginate(40)->appends($request->except('page'));

        return XePresenter::make('xe_poll::views.setting.index', [
            'items' => $items,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'userEmail' => $userEmail,
        ]);
    }
}
