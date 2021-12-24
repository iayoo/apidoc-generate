<?php


namespace Iayoo\ApidocGenerate\provider\thinkphp;


use Iayoo\ApidocGenerate\ControllerReflection;
use think\event\RouteLoaded;
use think\Request;
use think\Route;

class Parser
{
    protected $routes = [];

    protected $route;

    protected $isMultiApp = false;

    public function __construct()
    {
        $this->route = app()->make(Route::class);
    }

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
        $this->isMultiApp = true;
        $appDir = app()->getRootPath() . 'app' . DIRECTORY_SEPARATOR;
        if ($handle = opendir($appDir)) {
            while (false !== ($file = readdir($handle))) {
                if (in_array($file, ['.', '..'])) {
                    continue;
                }
                $routePath = $appDir . $file . DIRECTORY_SEPARATOR . 'route' . DIRECTORY_SEPARATOR;
                if (is_dir($routePath)){
                    $files = glob($routePath . '*.php');
                    foreach ($files as $routeFile) {
                        include $routeFile;
                        $this->getRouteFromFramework($file);
                    }
                }
            }
            closedir($handle);
            return true;
        }
    }

    /**
     * 解析路由
     * @param $route
     */
    protected function parserThinkPhpRoute($route){
        $routeInfo = explode("/",$route);
        $controller = '';
        $index = 0;
        foreach ($routeInfo as $path){
            $pathInfo = explode('.',$path);
            if ($index == 0){
                $controller = implode("\\",$pathInfo);
            }
            $index++;
        }
        $method = $routeInfo[count($routeInfo)-1];
        return compact('controller','method');
    }

    /**
     * 从框架加载路由配置信息
     * @param string $appName
     */
    protected function getRouteFromFramework($appName = 'api'){
        $routes = $this->route->getRuleName()->getRuleList();
        $this->routes[$appName] = [];

        foreach ($routes as $route){
            $ruleInfo = $this->route->getRuleName()->getRule($route['rule']);
            $group = "default";
            foreach ($ruleInfo as $itemInfo){
                $group = empty($itemInfo->getParent()->getFullName())?$group:$itemInfo->getParent()->getFullName();
            }
            if (!isset($this->routes[$appName][$group])){
                $this->routes[$appName][$group] = [];
            }
            $controlerRef = new ControllerReflection();
            if ($this->isMultiApp){
                $loadClass = "app\\{$appName}\\controller\\" ;
            }
            $controller = $this->parserThinkPhpRoute($route['route']);
            $classRes = $controlerRef->setClass($loadClass . $controller['controller']  );
            if ($classRes !== true){
                continue;
            }
            $controll_comment = $controlerRef->getClassCommentData();
            $controlerRef->setMethod($controller['method']);
            $method_comment = $controlerRef->parserMethod();
            $params = [];
            if (isset($method_comment['metadata']['validate'])){
                $params = $this->parserParamsFromValidate($method_comment['metadata']['validate']);
            }
            if (isset($method_comment['metadata']) && isset($method_comment['metadata']['title'])){
                $name = $method_comment['metadata']['title'];
            }
            $this->routes[$appName][$group][] = [
                'rule'             => $route['rule'],
                'method'           => $route['method'],
                'name'             => $name ?? $route['name'],
                'route'            => $route['route'],
                'controll_comment' => $controll_comment,
                'params'           => $params,
            ];
        }

        $this->route->clear();
    }

    /**
     * 从验证器中获取请求参数
     * @param $validate string 格式：class.scene
     */
    protected function parserParamsFromValidate($validate){
        $class = explode('.',$validate);
        if (isset($class[0])){
            $vClass = new $class[0];
            return $vClass->scene($class[1])->getSceneRule();
        }
    }
    
    public function routeHandle($callback){
        $routes = $this->getRouteList();
        dump($this->routes);
    }
}