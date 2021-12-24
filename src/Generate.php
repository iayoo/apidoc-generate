<?php
/**
 *
 */


namespace Iayoo\ApidocGenerate;

/**
 * Class Generate
 * @author Iayoo
 * @package Iayoo\ApidocGenerate
 */
class Generate
{
    protected $outputDir;

    /**
     * @param mixed $outputDir
     */
    public function setOutputDir($outputDir): void
    {
        $this->outputDir = $outputDir;
    }

    
}