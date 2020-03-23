@section('page_title')
    <h2>{{xe_trans('poll::poll')}}</h2>
@stop

<div class="container-fluid container-fluid--part site-manager">
    <div class="row">
        <div class="col-sm-12">
            <div class="panel-group">
                <div class="panel">
                    <div class="panel-heading">
                        <div class="pull-left">
                            <h3 class="panel-title">
                            </h3>
                        </div>
                        <div class="pull-right">
                            <div class="input-group search-group">
                                <form>
                                    <div class="search-btn-group">
                                        <div class="input-group-btn">
                                            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><i class="xi-calendar-check"></i></button>
                                        </div>
                                        <div class="search-input-group">
                                            <input type="text" name="start_date" class="form-control" placeholder="{{xe_trans('xe::enterStartDate')}}" value="{{ $startDate }}" >
                                            <input type="text" name="end_date" class="form-control" placeholder="{{xe_trans('xe::enterEndDate')}}" value="{{ $endDate }}" >
                                        </div>
                                    </div>

                                    <div>
                                        <div class="search-input-group">
                                            <input type="text" name="user_email" class="form-control" aria-label="Text input with dropdown button" placeholder="{{xe_trans('xe::enterEmail')}}" value="{{Request::get('user_email')}}">
                                            <button class="btn-link">
                                                <i class="xi-search"></i><span class="sr-only">{{xe_trans('xe::search')}}</span>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                            <tr>
                                <th scope="col">{{xe_trans('xe::title')}}</th>
                                <th scope="col">{{xe_trans('xe_poll::requiredItemCount')}}</th>
                                <th scope="col">{{xe_trans('xe::writer')}}</th>
                                <th scope="col">{{xe_trans('xe::date')}}</th>
                                <th scope="col">{{xe_trans('xe_poll::expiredDate')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($items as $item)
                                <tr>
                                    <td>{{ $item->title }}</td>
                                    <td>{{ $item->check_count}}</td>
                                    <td>{{ $item->poll_count}}</td>
                                    <td>{{ $item->user->getDisplayName() }}</td>
                                    <td>{{ $item->created_at->format('Y.m.d H:i:s') }}</td>
                                    <td>{{ $item->expired_at->format('Y.m.d H:i:s') }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($pagination = $items->render())
                        <div class="panel-footer">
                            <div class="pull-left">
                                <nav>
                                    {!! $pagination !!}
                                </nav>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
