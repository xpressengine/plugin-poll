<?php
namespace Xpressengine\Plugins\Poll;

use Route;
use Xpressengine\Plugin\AbstractPlugin;
use Illuminate\Database\Schema\Blueprint;
use Schema;
use Xpressengine\Config\ConfigEntity;
use Xpressengine\User\UserInterface;
use Xpressengine\Plugins\Board\Models\Board;
use Xpressengine\Plugins\Comment\Models\Comment;

class Plugin extends AbstractPlugin
{
    public function register()
    {
        app()->singleton(Handler::class, function ($app) {
            $proxyClass = app('xe.interception')->proxy(Handler::class, 'XePoll');
            return new $proxyClass();
        });
        app()->alias(Handler::class, 'xe.xe_poll.handler');
    }

    /**
     * 이 메소드는 활성화(activate) 된 플러그인이 부트될 때 항상 실행됩니다.
     *
     * @return void
     */
    public function boot()
    {
        $this->route();
        $this->registerIntercepts();
    }

    protected function route()
    {
        // settings menu 등록
        $menus = [
            'contents.poll' => [
                'title' => 'poll::poll',
                'display' => true,
                'description' => '',
                'ordering' => 20000
            ],
        ];
        foreach ($menus as $id => $menu) {
            app('xe.register')->push('settings/menu', $id, $menu);
        }

        Route::settings(
            $this->getId(),
            function () {
                Route::group(
                    ['namespace' => 'Xpressengine\Plugins\Poll\Controllers'],
                    function () {
                        Route::get(
                            '/',
                            [
                                'as' => 'xe_poll::setting.index',
                                'uses' => 'SettingController@index',
                                'settings_menu' => 'contents.poll',
                            ]
                        );
                    }
                );
            }
        );

        Route::fixed(self::getId(), function () {
            Route::get('/tool/popup', ['as' => 'xe_poll::editor_tool.popup', 'uses' => 'UserController@toolPopup']);
            Route::post('/tool/store', ['as' => 'xe_poll::editor_tool.store', 'uses' => 'UserController@toolStore']);
            Route::post('/tool/join/{id}', ['as' => 'xe_poll::editor_tool.join', 'uses' => 'UserController@toolJoin']);
        }, ['namespace' => 'Xpressengine\Plugins\Poll\Controllers']);
    }

    protected function registerIntercepts()
    {
        // board - write document
        intercept(
            '\Xpressengine\Plugins\Board\Handler@add',
            'xe_poll.board-add',
            function ($func, array $args, UserInterface $user, ConfigEntity $config) {
                \XeDB::beginTransaction();
                /** @var Board $board */
                $board = $func($args, $user, $config);

                $request = app('request');
                if ($request->has('_polls') !== false) {
                    $pollHandler = app('xe.xe_poll.handler');
                    $pollHandler->bind($request->get('_polls'), $board->id, get_class($board));
                }
                \XeDB::commit();
                return $board;
            }
        );

        // board - write document
        intercept(
            '\Xpressengine\Plugins\Board\Handler@put',
            'xe_poll.board-put',
            function ($func, Board $board, array $args, ConfigEntity $config) {
                \XeDB::beginTransaction();
                /** @var Board $board */
                $board = $func($board, $args, $config);

                $request = app('request');
                if ($request->has('_polls') !== false) {
                    $pollHandler = app('xe.xe_poll.handler');
                    $pollHandler->bind($request->get('_polls'), $board->id, get_class($board));
                }
                \XeDB::commit();
                return $board;
            }
        );

        // comment - write comment
        intercept(
            'Xpressengine\Plugins\Comment\Handler@create',
            'xe_poll.comment-create',
            function ($func, $inputs, $user = null) {
                \XeDB::beginTransaction();
                /** @var Comment $comment */
                $comment = $func($inputs, $user);

                $request = app('request');
                if ($request->has('_polls') !== false) {
                    $pollHandler = app('xe.xe_poll.handler');
                    $pollHandler->bind($request->get('_polls'), $comment->id, get_class($comment));
                }
                \XeDB::commit();
                return $comment;
            }
        );

        // comment - write comment
        intercept(
            'Xpressengine\Plugins\Comment\Handler@put',
            'xe_poll.comment-put',
            function ($func, $comment) {
                \XeDB::beginTransaction();
                /** @var Comment $comment */
                $comment = $func($comment);

                $request = app('request');
                if ($request->has('_polls') !== false) {
                    $pollHandler = app('xe.xe_poll.handler');
                    $pollHandler->bind($request->get('_polls'), $comment->id, get_class($comment));
                }
                \XeDB::commit();
                return $comment;
            }
        );
    }

    public function getSettingsURI()
    {
        return route('xe_poll::setting.index');
    }

    /**
     * 플러그인이 활성화될 때 실행할 코드를 여기에 작성한다.
     *
     * @param string|null $installedVersion 현재 XpressEngine에 설치된 플러그인의 버전정보
     *
     * @return void
     */
    public function activate($installedVersion = null)
    {
        // implement code
        $trans = app('xe.translator');
        $trans->putFromLangDataSource('xe_poll', base_path('plugins/xe_poll/langs/lang.php'));
    }

    /**
     * 플러그인을 설치한다. 플러그인이 설치될 때 실행할 코드를 여기에 작성한다
     *
     * @return void
     */
    public function install()
    {
        if (!Schema::hasTable('polls')) {
            Schema::create(
                'polls',
                function (Blueprint $table) {
                    $table->engine = "InnoDB";

                    $table->increments('id');
                    $table->string('user_id', 36)->index();
                    $table->string('target_id', 36)->index();
                    $table->string('target_type')->nullable();
                    $table->bigInteger('poll_count')->default(0);
                    $table->string('title', 255);
                    $table->string('ipaddress', 128)->index();
                    $table->timestamp('expired_at');
                    $table->timestamp('created_at');
                    $table->timestamp('updated_at');
                }
            );
        }

        if (!Schema::hasTable('poll_items')) {
            Schema::create(
                'poll_items',
                function (Blueprint $table) {
                    $table->engine = "InnoDB";

                    $table->increments('id');
                    $table->bigInteger('poll_id')->index();
                    $table->integer('check_count')->default(1);
                    $table->bigInteger('poll_count')->default(0);
                    $table->string('title', 255);
                }
            );
        }

        if (!Schema::hasTable('poll_options')) {
            Schema::create(
                'poll_options',
                function (Blueprint $table) {
                    $table->engine = "InnoDB";

                    $table->increments('id');
                    $table->bigInteger('poll_item_id')->index();
                    $table->bigInteger('poll_count')->default(0);
                    $table->string('title', 255);
                }
            );
        }

        if (!Schema::hasTable('poll_logs')) {
            Schema::create(
                'poll_logs',
                function (Blueprint $table) {
                    $table->engine = "InnoDB";

                    $table->increments('id');
                    $table->bigInteger('poll_id')->index();
                    $table->string('user_id', 36)->index();
                    $table->string('ipaddress', 128)->index();
                    $table->text('options_ids');
                    $table->timestamp('created_at');
                }
            );
        }
    }

    /**
     * 해당 플러그인이 설치된 상태라면 true, 설치되어있지 않다면 false를 반환한다.
     * 이 메소드를 구현하지 않았다면 기본적으로 설치된 상태(true)를 반환한다.
     *
     * @return boolean 플러그인의 설치 유무
     */
    public function checkInstalled()
    {
        // implement code

        return parent::checkInstalled();
    }

    /**
     * 플러그인을 업데이트한다.
     *
     * @return void
     */
    public function update()
    {
        // implement code
        if (!Schema::hasColumn('polls', 'target_type')) {
            Schema::table('polls', function ($table) {
                $table->string('target_type')->nullable();
            });
        }
    }

    /**
     * 해당 플러그인이 최신 상태로 업데이트가 된 상태라면 true, 업데이트가 필요한 상태라면 false를 반환함.
     * 이 메소드를 구현하지 않았다면 기본적으로 최신업데이트 상태임(true)을 반환함.
     *
     * @return boolean 플러그인의 설치 유무,
     */
    public function checkUpdated()
    {
        // implement code
        if (!Schema::hasColumn('polls', 'target_type')) {
            return false;
        }

        return parent::checkUpdated();
    }
}
