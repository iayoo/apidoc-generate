<?php
/**
 *
 */


namespace Iayoo\ApidocGenerate\provider\thinkphp;


class ApiDocValidate extends \think\Validate
{
    /**
     * @return array
     */
    public function getSceneRule(): array
    {
        $rules = [];
        foreach ($this->scene[$this->currentScene] as $item){
            $mess = [];
            if (isset($this->rule[$item])){
                $regArr = explode("|",$this->rule[$item]);
                foreach ($regArr as $regItem){
                    if (isset($this->field["{$item}"])){
                        $mess[] = $this->field["{$item}"];
                    }
                }
            }
            $rules[] = [
                'field'=>$item,
                'info'=>implode("|",$mess),
            ];
        }
        return $rules;
    }
}