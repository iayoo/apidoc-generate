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
     * @return bool
     */
    protected function loadRoutes(): bool
    {
        if ($this->loadMutilAppRoutes() === true){
            return true;
        }
        // 加载路由定义
        $routePath = app()->getRootPath() . 'route' . DIRECTORY_SEPARATOR;
        if (is_dir($routePath)) {
            $files = glob($routePath . '*.php');
            foreach ($files as $file) {
                include $file;
            }
        }
    }

    /**
     * 加载多应用模式下的路由
     */
    protected function loadMutilAppRoutes(){
        if (!class_exists("think\app\MultiApp")){
            return false;
        }
        $appDir = app()->getRootPath() . 'app' . DIRECTORY_SEPARATOR;
        if ($handle = opendir($appDir)) {
            while (false !== ($file = readdir($handle))) {
                if (in_array($file, ['.', '..'])) {
                    continue;
                }
                $routePath = $appDir . $file . DIRECTORY_SEPARATOR . 'route' . DIRECTORY_SEPARATOR;
                if (is_dir($routePath)){
                    $files = glob($routePath . '*.php');
                    foreach ($files as $file) {
                        include $file;
                    }
                }
            }
            closedir($handle);
            return true;
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