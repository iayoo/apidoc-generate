<?php


namespace Iayoo\ApidocGenerate\provider\thinkphp;


use think\event\RouteLoaded;
use think\Request;
use think\Route;

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
            // 判断终端运行,thinkphp 终端模式下是不会加载路由的,因此我们需要手动加载一次
            $this->loadRoutes();
        }
        return $router->getRuleName()->getRuleList();
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
    
    public function routeHandle($callback){
        $routes = $this->getRouteList();
        $router = app()->make(Route::class);

        $postmanItemList = [];
        foreach ($routes as $route){
            $ruleInfo = $router->getRuleName()->getRule($route['rule']);
            $group = "default";
            foreach ($ruleInfo as $itemInfo){
                dump($itemInfo);
                $group = $itemInfo->getParent()->getFullName();
                if (empty($group)){
                    $group = "default";
                }
            }
            if (strstr($route['rule'],"<MISS>")!==false){
                continue;
            }
            if ($callback instanceof \Closure){
                $callback($route['rule'],$route['method'],[],$group,'');
            }
        }
    }
}