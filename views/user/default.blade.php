@if ($javascriptInit == true)
<style>
    .__xe_poll_section {max-width: 500px;}
    .poll-message {background-color:#D9EDF7; color:#3A87AD; border-color:#BCE8F1;}
    .poll-header {background-color:#444; color:#fff;}
    .poll-body {border:1px solid #444; padding: 5px 10px;}
    .poll-body {border:1px solid #444; padding: 5px 10px;}
    .poll-body ul { list-style:none;}
    .progress{background-color:#dee2e6;height:20px; width:100%;}
    .progress-bar{background-color:#17a2b8;height:20px; width:0%;}

    .poll-question {border-bottom:1px solid #888; font-weight:bold;}
    /*.poll-option ul {margin-top: 3px;}*/
    .poll-option li:nth-last-child(1) {margin-bottom: 10px;}
</style>
@endif
<div class="__xe_poll_section">
    @if ($isExpired == false && $isJoined == false)
    <div class="__xe_poll_survey" data-id="{{$poll->id}}">
        <form class="__xe_poll_form" method="post" action="{{route('xe_poll::editor_tool.join', ['id' => $poll->id])}}">
            <input type="hidden" name="return_url" value="{{$current_url}}" />
            <input type="hidden" name="id" value="{{$poll->id}}" />
            <div class="poll-body">
                <div class="poll-header">
                    <p class="title">투표 : {{$poll->title}} (참여자: {{$poll->poll_count}})</p>
                    <p class="date">설문조사 종료일 : {{$poll->expired_at->format('Y-m-d')}}</p>
                </div>

                @foreach($poll->items as $item)
                <div class="poll-item" data-item-id="{{$item->id}}" data-check-count="{{$item->check_count}}">
                    <p class="poll-question">질문 : {{$item->title}} @if ($item->check_count > 1)({{$item->check_count}}개 선택)@endif</p>

                    <ul class="poll-option">
                        @foreach($item->options as $option)
                            <li>
                                @if ($item->check_count > 1)
                                    <input type="checkbox" class="select_option" name="option_{{$item->id}}[]" value="{{$option->id}}"/>
                                @else
                                    <input type="radio" class="select_option" name="option_{{$item->id}}[]" value="{{$option->id}}"/>
                                @endif
                                <span class="title">{{$option->title}}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
                @endforeach

                <div class="poll-footer">
                    <button type="button" class="xe-btn xe-btn-normal btn-goto-result">결과 보기</button>
                    <button type="submit" class="xe-btn xe-btn-primary">설문 참여</button>
                </div>
            </div>

        </form>
    </div>
    @endif
    <div class="__xe_poll_survey_result" data-id="{{$poll->id}}" @if ($isExpired == false && $isJoined == false)style="display:none;"@endif >
        @if ($isExpired == true)
            <p class="poll-expired poll-message">투표가 종료되었습니다.</p>
        @elseif ($isJoined == true)
            <p class="poll-joined poll-message">투표에 참여했습니다.</p>
        @endif

        <div class="poll-body">
            <div class="poll-header">
                <p class="title">투표 : {{$poll->title}} (참여자: {{$poll->poll_count}})</p>
                <p class="date">설문조사 종료일 : {{$poll->expired_at->format('Y-m-d')}}</p>
            </div>

            @foreach($poll->items as $item)
                <div class="poll-item" data-item-id="{{$item->id}}" data-check-count="{{$item->check_count}}">
                    <p class="poll-question">질문 : {{$item->title}} @if ($item->check_count > 1)({{$item->check_count}}개 선택)@endif</p>

                    <ul class="poll-option">
                        @foreach($item->options as $option)
                            <li>
                                <span class="title">{{$option->title}}</span>
                                @if ($item->poll_count == 0)
                                    {{$option->poll_count}} (0 %)
                                    <div class="progress">
                                        <div class="progress-bar"></div>
                                    </div>
                                @else
                                    {{$option->poll_count}} ({{(int)(($option->poll_count / $item->poll_count) * 100)}} %)
                                    <div class="progress">
                                        <div class="progress-bar" style="width:{{(int)(($option->poll_count / $item->poll_count) * 100)}}%;"></div>
                                    </div>
                                @endif

                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
            @if ($isExpired == false && $isJoined == false)
                <div class="poll-footer">
                    <button type="button" class="xe-btn xe-btn-normal btn-goto-poll">돌아가기</button>
                </div>
            @endif
        </div>
    </div>
</div>


@if ($javascriptInit == true)
{{app('xe.frontend')->html('xe_poll.default.form_submit')->content("<script>
$(function () {
    $('.__xe_poll_form').bind('submit', function (e) {
        var f = $(e.target);
        var check = true;
        f.find('.poll-item').each(function(i, v) {
            var o = $(v);
            var selectedCount = o.find('.select_option:checked').length;
            o.find('.poll-question').removeClass('caution').css('font-weight', 'normal');
            if (o.data('check-count') > selectedCount) {
                console.log(o.find('.poll-question'));
                o.find('.poll-question').addClass('caution').css('font-weight', 'bold');
                check = false;
            }
        });

        if (check == false) {
            alert('답변 항목 수를 확인하세요.');
            return false;
        }

        return true;
    });

    $('.btn-goto-result').bind('click', function(e) {
        var o = $(e.target).closest('.__xe_poll_section')
        o.find('.__xe_poll_survey').hide();
        o.find('.__xe_poll_survey_result').show();
    });
    $('.btn-goto-poll').bind('click', function(e) {
        var o = $(e.target).closest('.__xe_poll_section')
        o.find('.__xe_poll_survey_result').hide();
        o.find('.__xe_poll_survey').show();
    });

});
</script>")->load()}}
@endif