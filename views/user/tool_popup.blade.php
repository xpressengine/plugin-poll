<div class="container">
    <h2>{{xe_trans($title)}}</h2>

    <div class="tab-content">
        <div id="home" class="tab-pane fade in active">
            <div class="panel panel-primary poll-panel">
                <form class="form-poll">
                    <div class="panel-body">
                        <div class="form-group">
                            <label>{{xe_trans('xe_poll::pollTitle')}}</label>
                            <input type="text" name="poll_title" class="form-control" value="">
                        </div>
                        <div class="form-group">
                            <label>{{xe_trans('xe_poll::expiredDate')}}</label>
                            <input type="text" name="expired_at" class="form-control" value="{{$expiredAt}}">
                        </div>
                        <div class="form-group">
                            <label>{{xe_trans('xe_poll::pollSkin')}}</label>
                            <select name="skin_name" class="form-control">
                                <option value="default">{{xe_trans('xe_poll::defaultSkin')}}</option>
                            </select>
                        </div>
                    </div>
                </form>
                <form class="form-item">
                    <hr/>
                    <div class="panel-body">
                        <div class="form-group">
                            <label>{{xe_trans('xe_poll::surveyTitle')}}</label>
                            <input type="text" name="survey_title" class="form-control" required="required">
                        </div>
                        <div class="form-group">
                            <label>{{xe_trans('xe_poll::selectOptionCount')}}</label>
                            <input type="number" name="check_count" class="form-control" value="1">
                        </div>
                        <div class="options">
                            <div class="form-group">
                                <label>{{xe_trans('xe_poll::option')}}</label> <label class="option-num">1</label>
                                <input type="text" name="option_title[]" class="form-control" required="required">
                            </div>
                            <div class="form-group">
                                <label>{{xe_trans('xe_poll::option')}}</label> <label class="option-num">2</label>
                                <input type="text" name="option_title[]" class="form-control" required="required">
                            </div>
                        </div>
                        <div class="clearfix">
                            <button type="button" class="btn btn-default pull-left btn-add-option" onclick="addOption(this);">{{xe_trans('xe_poll::AddOption')}}</button>
                            <button type="button" class="btn btn-default pull-left btn-remove-last-option" onclick="removeLastOption(this);">{{xe_trans('xe_poll::RemoveLastOption')}}</button>

                            <button type="button" class="btn btn-default pull-right btn-remove-survey" onclick="removeSurvey(this);">{{xe_trans('xe_poll::RemoveSurvey')}}</button>
                        </div>
                    </div>
                </form>
                <div class="panel-footer">
                    <div class="clearfix">
                        <button type="button" class="btn btn-default pull-right btn-close-popup">닫기</button>
                        <button type="button" class="btn btn-default pull-right btn-add-survey">{{xe_trans('xe_poll::AddSurvey')}}</button>
                        <button type="button" id="btnAppendGeneralContent" class="btn btn-primary pull-right poll-btn-create">입력</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    var editortoolXePoll = (function($) {
        var _this;

        var _defaultSurvey = '';
        var _defaultOption = '';

        return {
            init: function(targetEditor, appendToolContent) {
                _this = this;

                this.cache();
                this.bindEvents();

                this.targetEditor = targetEditor;
                this.appendToolContent = appendToolContent;

                return this;
            },
            cache: function() {
                _defaultSurvey = $('.form-item').html();
                _defaultOption = $('.options .form-group').html();

                this.$form = $('.tool-form');

                this.$btnClosePopup = $('.btn-close-popup');
                this.$btnAddSurvey = $('.btn-add-survey');
                this.$btnAppendContent = $('.poll-btn-create');
            },
            bindEvents: function() {
                this.$btnClosePopup.on('click', function() {
                    self.close();
                });
                this.$btnAddSurvey.on('click', this.addSurvey)
                this.$btnAppendContent.on('click', this.appendContent);
            },
            addOption : function (e) {
                var $e = $(e);
                var $f = $e.closest('.form-item');
                var num = $f.find('.options .form-group').length;
                var $option = $('<div class="form-group"></div>').append(_defaultOption);
                $option.find('.option-num').text(num+1);
                $f.find('.options').append($option);
            },
            removeLastOption : function (e) {
                var $e = $(e);
                var $f = $e.closest('.form-item');
                var $names = $f.find('[name="option_title[]"]');
                console.log($names);
                if ($names.length <= 2) {
                    alert('항목은 2개 이상 작성해야 합니다.');
                } else {
                    $names[$names.length-1].closest('.form-group').remove();
                }
            },
            removeSurvey : function (e) {
                var $e = $(e);
                if ($('.poll-panel .form-item').length < 2) {
                    alert('설문은 1개 이상 필요합니다.');
                } else {
                    $e.closest('.form-item').remove();
                }
            },
            addSurvey : function () {
                var form = $('<form class="form-item"></form>');
                form.append(_defaultSurvey);
                var $f = $('.form-item');
                var num = $f.length;
                if (num == 1) {
                    $f.after(form);
                } else if (num > 1) {
                    $f.last().after(form);
                }

            },
            validate : function () {
                if ($('[name="expired_at"]').val() == '') {
                    alert('종료일을 입력하세요. ');
                    $('[name="expired_at"]').focus();
                    return false;
                }

                var result = true;
                $('.form-item input').each(function (i, v) {
                    if ($(v).val() == '') {
                        result = false;
                    }
                });
                if (result == false) {
                    alert('제목 및 항목을 입력하세요.');
                    return false;
                }

                $('.form-item [name="check_count"]').each(function (i, v) {
                    var checkCount = parseInt($(v).val());
                    if (checkCount < 1) {
                        result = false;
                    }
                });
                if (result == false) {
                    alert('선택 항목 수를 확인하세요.');
                    return false;
                }

                return true;
            },
            isValid: function(obj) {
                var flag = true;
                var parser = this.getParser(obj.url);

                if(!window.XE.Utils.isURL(obj.url)) {
                    alert('url 형식이 맞지 않습니다.');
                    flag = false;

                } else if (!this.checkWhiteList(parser)) {
                    alert('신뢰되지 않는 도메인입니다.');
                    flag = false;
                }

                return flag;
            },
            appendContent: function() {
                if(_this.validate()) {
                    var params = {};
                    params['expired_at'] = $('[name="expired_at"]').val();
                    params['poll_title'] = $('[name="poll_title"]').val();
                    params['skin_name'] = $('[name="skin_name"] option:selected').val();

                    var surveyCount = $('.form-item').length;
                    params['survey_count'] = surveyCount;

                    $('.form-item').each(function (i, v) {
                        var serialize = $(v).serializeArray();
                        console.log(serialize);
                        $(serialize).each(function (i2, v2) {
                            if (v2['name'] == 'survey_title') {
                                v2['name'] = v2['name'] + '_' + i;
                                params[v2['name']] = v2['value'];
                            } else if (v2['name'] == 'check_count') {
                                v2['name'] = v2['name'] + '_' + i;
                                params[v2['name']] = v2['value'];
                            } else {
                                var keyName = 'option_title_' + i;
                                if (params[keyName] == undefined) {
                                    params[keyName] = [];
                                }
                                params[keyName].push(v2['value']);
                            }
                        });
                    });

                    $.ajax({
                        url:'{{ $routeStore }}',
                        method: 'post',
                        data : params,
                        success:function(data) {
                            // alert(data);
                            console.log(data);
                            var pollId = data.id;
                            var widget = [
                                '<p class="xe-poll-widget">',
                                '<div>'+ params['poll_title'] +'</div>',
                                '<img src="/plugins/xe_poll/assets/imgs/blank.gif" class="__xe_poll" data-id="'+pollId+'" data-skin="'+params['skin_name']+'" ',
                                ' style="display:block;width:400px;height:300px;border:2px dotted #4371B9;background:url(/plugins/xe_poll/assets/imgs/poll_maker_component.gif) no-repeat center;" >',
                                '</p>'
                            ].join('');

                            _this.appendToolContent(widget);

                            // 폼에 _polls 필드 추가
                            var $form = $(_this.targetEditor.container.$).closest('form')
                            var $fields = $form.find('[name="_polls[]"]')
                            if (!$fields.filter('[value=' + pollId + ']').length) {
                                $form.prepend('<input type="hidden" name="_polls[]" value="' + pollId + '" />')
                            }

                            self.close();
                        }
                    });
                }
            }
        }
    })(window.jQuery);

    window.jQuery(function($) {
        window.opener.XEeditor.$$emit('editorTools.XePollTool.popup', window.editortoolXePoll);

    });
    var addOption = function(e) {
        window.editortoolXePoll.addOption(e);
    };
    var removeLastOption = function(e) {
        window.editortoolXePoll.removeLastOption(e);
    };
    var removeSurvey = function(e) {
        window.editortoolXePoll.removeSurvey(e);
    };
</script>
