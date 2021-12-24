<?php


namespace Iayoo\ApidocGenerate\provider\thinkphp;

use think\console\Input;
use think\console\Output;

/**
 * Class ApiDocGenerateCommand
 * @author Iayoo
 * @package Iayoo\ApidocGenerate\provider\thinkphp
 */
class ApiDocGenerateCommand extends \think\console\Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('API DOC 生成指令')
            ->setDescription('API DOC 生成指令');
    }

    protected function execute(Input $input, Output $output)
    {
        $generate = new PostmanGenerate();
        $generate->make();
    }
}