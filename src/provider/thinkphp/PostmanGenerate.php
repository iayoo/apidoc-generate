<?php
namespace Iayoo\ApidocGenerate\provider\thinkphp;

use Iayoo\ApidocGenerate\Generate;

class PostmanGenerate extends Generate
{

    protected $postmanId;

    protected $name = 'export';

    protected $schema = 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json';

//    protected $baseData = [
//        'info'=>[
//            "_postman_id"=> time(),
//            "name"=> "单商户",
//            "schema"=> "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
//        ],
//        'item'=>[
////                [
////                    "name"=>"api 导出",
////                    "item"=>$postmanItemList,
////                ]
//        ]
//    ];

    public function make(){
        $p =  new Parser();
        $postData = [];
        $p->routeHandle(function ($rule,$method,$params,$data) use (&$postData){
            if (!isset($postData[$data['group']])){
                $postData[$data['group']] = [];
            }
            if (strstr($rule,"<MISS>")!==false){
                return;
            }
            $requestParams = [];
            if ($params){
                if (strtoupper($method) === "POST"){
                    $requestParams = [
                        'mode'=>'urlencoded',
                        'urlencoded'=>[]
                    ];
                    foreach ($params as $param){
                        $requestParams['urlencoded'][] = [
                            'key'   => $param['field'],
                            'value' => '',
                            'type'  => 'text',
                        ];
                    }
                }
            }

            $postData[$data['group']][] =[
                'name'=>$data['name'],
                'request'=>[
                    'method'=>strtoupper($method)=="*"?"GET":strtoupper($method),
                    "header" => [],
                    "url" => [
                        "raw"  => "{{uri}}{$rule}",
                        "host" => [
                            "{{uri}}"
                        ],
                        "path" => explode('/',$rule)
                    ],
                    "body"=>$requestParams,
                    "response"=>[]
                ],
            ];
        });
        $outputFile = '';
        $time = time();

        if ($this->outputDir && is_dir($this->outputDir)){
            $outputFile = $this->outputDir . DIRECTORY_SEPARATOR . "api-doc.postman-{$time}.json";
        }else{
            $outputFile = "api-doc.postman-{$time}.json";
        }
        file_put_contents($outputFile,json_encode([
            'info'=>[
                '_postman_id'=>$time,
                'name'=>"api-doc.postman-{$time}.json",
                'schema'=>$this->schema,
            ],
            'item'=>[
                [
                    'name'=>'api 导出',
                    'item'=>$postData,
                ]
            ]
        ],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
    }
}