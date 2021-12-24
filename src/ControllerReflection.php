<?php
namespace Iayoo\ApidocGenerate;

class ControllerReflection
{
    protected $class = '';

    /** @var ReflectionClass  */
    protected $reflectClass;
    /** @var DocParser */
    protected $docParser;

    protected $classCommentData = [];

    protected $method;

    /**
     * @return string
     */
    public function getClass(): string
    {
        return $this->class;
    }

    /**
     * @param string $class
     */
    public function setClass(string $class): void
    {
        $this->class = $class;
        $this->init();
    }

    protected function init(){
        if (!$this->reflectClass){
            $this->reflectClass = null;
        }
        $this->reflectClass = new \ReflectionClass ( $this->class );
        $this->docParser = new DocParser();
        $this->classCommentData = $this->docParser->parse( $this->reflectClass->getDocComment());
    }

    /**
     * @return array
     */
    public function getClassCommentData(): array
    {
        return $this->classCommentData;
    }

    /**
     * @param mixed $method
     */
    public function setMethod($method): void
    {
        $this->method = $method;
    }

    public function parserMethod(){
        //获取类中的方法，设置获取public,protected类型方法
        $methods = $this->reflectClass->getMethods(\ReflectionMethod::IS_PUBLIC + \ReflectionMethod::IS_PROTECTED + \ReflectionMethod::IS_PRIVATE);
//遍历所有的方法
        foreach ($methods as $method) {
            if ($method->getName() !== $this->method){
                continue;
            }
            $arguments = [];
            $defaults = [];
            $class_metadata = [];
            //获取方法的注释
            $doc = $method->getDocComment();
            //解析注释
            $info = $this->docParser->parse($doc);
            $metadata = $class_metadata +  $info;
            //获取方法的类型
            $method_flag = $method->isProtected();//还可能是public,protected类型的
            //获取方法的参数
            $params = $method->getParameters();
            $position=0;    //记录参数的次序
            foreach ($params as $param){
                $arguments[$param->getName()] = $position;
                //参数是否设置了默认参数，如果设置了，则获取其默认值
                $defaults[$position] = $param->isDefaultValueAvailable() ? $param->getDefaultValue() : NULL;
                $position++;
            }

            $call = array(
                'method_name'=>$method->getName(),
                'arguments'=>$arguments,
                'defaults'=>$defaults,
                'metadata'=>$metadata,
                'method_flag'=>$method_flag
            );
            return $call;
        }
    }

}