<?php
namespace Xpressengine\Plugins\Poll;

use Route;
use Xpressengine\Plugin\AbstractPlugin;
use Illuminate\Database\Schema\Blueprint;
use Schema;

class Plugin extends AbstractPlugin
{
    public function register()
    {
        app()->singleton(Handler::class, function ($app) {
            $proxyClass = app('xe.interception')->proxy(Handler::class, 'Point');
            return new $proxyClass($this, app('xe.config'));
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

        return parent::checkUpdated();
    }
}
