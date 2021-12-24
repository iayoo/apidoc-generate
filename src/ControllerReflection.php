<?php
namespace Iayoo\ApidocGenerate;

class ControllerReflection
{
    protected $class = '';

    /** @var ReflectionClass  */
    protected $reflectClass;

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
        $this->reflectClass = new ReflectionClass ( $this->class );

    }

    public function getName(){
        dump($this->reflectClass->getDocComment());
    }

}