<?php

use app\http\middleware\InstallMiddleware;
use think\facade\Route;
use think\Response;

/**
 * 系统默认路由配置
 */
Route::get('install/index', 'InstallController/index');//安装程序
Route::post('install/index', 'InstallController/index');//安装程序
Route::get('install/compiler', 'InstallController/swooleCompiler');//swooleCompiler安装提示
Route::get('upgrade/index', 'UpgradeController/index');
Route::get('up', 'UpgradeVersionController/index');
Route::get('up/render', 'UpgradeVersionController/render');
Route::get('upgrade/upgrade', 'UpgradeController/upgrade');
Route::get('upgrade/transfer', 'UpgradeController/transfer');
Route::post('upgrade/upgrade_transfer', 'UpgradeController/upgrade_transfer');

Route::group('/', function () {
    Route::group('install', function () {
        Route::miss(function () {
            return view(app()->getRootPath() . 'public' . DS . 'install');
        });
    });

    Route::group('admin', function () {
        Route::miss(function () {
            $pathInfo = request()->pathinfo();
            $pathInfoArr = explode('/', $pathInfo);
            $admin = $pathInfoArr[0] ?? '';
            if ($admin === 'admin') {
                return view(app()->getRootPath() . 'public' . DS . 'admin' . DS . 'index.html');
            } else {
                return Response::create()->code(404);
            }
        });
    });

    Route::group('kefu', function () {
        Route::miss(function () {
            $pathInfo = request()->pathinfo();
            $pathInfoArr = explode('/', $pathInfo);
            $admin = $pathInfoArr[0] ?? '';
            if ('kefu' === $admin) {
                return view(app()->getRootPath() . 'public' . DS . 'admin' . DS . 'index.html');
            } else {
                return Response::create()->code(404);
            }
        });
    });

    Route::group('pages', function () {
        Route::miss(function () {
            $pathInfo = request()->pathinfo();
            $pathInfoArr = explode('/', $pathInfo);
            $admin = $pathInfoArr[0] ?? '';
            if ('pages' === $admin) {
                return view(app()->getRootPath() . 'public' . DS . 'index.html');
            } else {
                return Response::create()->code(404);
            }
        });
    });

    Route::group('home', function () {
        Route::miss(function () {
            if (request()->isMobile()) {
                return redirect(app()->route->buildUrl('/'));
            } else {
                return view(app()->getRootPath() . 'public' . DS . 'home' . DS . 'index.html');
            }
        });
    });

    Route::group('supplier', function () {
        Route::miss(function () {
            $pathInfo = request()->pathinfo();
            $pathInfoArr = explode('/', $pathInfo);
            $admin = $pathInfoArr[0] ?? '';
            if ('supplier' === $admin) {
                return view(app()->getRootPath() . 'public' . DS . 'supplier' . DS . 'index.html');
            } else {
                return Response::create()->code(404);
            }
        });
    });

    Route::miss(function () {
        if (!request()->isMobile() && is_dir(app()->getRootPath() . 'public' . DS . 'home') && !request()->get('type')) {
            if (file_exists(app()->getRootPath() . 'public' . DS . 'home' . DS . 'index.html')) {
				return view(app()->getRootPath() . 'public' . DS . 'home' . DS . 'index.html');
			} else {
				return view(app()->getRootPath() . 'public' . DS . 'index.html');
			}
        } else {
            return view(app()->getRootPath() . 'public' . DS . 'index.html');
        }
    });

})->middleware(InstallMiddleware::class);
