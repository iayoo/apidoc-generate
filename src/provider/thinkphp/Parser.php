<?php


namespace Iayoo\ApidocGenerate\provider\thinkphp;


use think\event\RouteLoaded;
use think\Request;

class Parser
{
    /**
     * 获取路由列表
     * @return array
     */
    public function getRouteList(){

        /** @var \think\Route $router */
        $router = app()->make(\think\Route::class);

        if (app()->runningInConsole()){
            // 判断终端运行,thinkphp 终端模式下是不会加载路由的
            $this->loadRoutes();
        }
        return $router->getRuleList();
    }

    /**
     * 加载路由
     * @access protected
     * @return void
     */
    protected function loadRoutes(): void
    {
        // 加载路由定义
        $routePath = app()->getRootPath() . 'route' . DIRECTORY_SEPARATOR;
        if (is_dir($routePath)) {
            $files = glob($routePath . '*.php');
            foreach ($files as $file) {
                include $file;
            }
        }
    }
}