<?php


//composer require "aliyuncs/oss-sdk-php": "^2.2"

require_once __DIR__ . '/../vendor/autoload.php';

$config = [
    'accessKey' => 'your key',
    'secret' => 'your secret',
    'endPoint' => 'oss-cn-shanghai.aliyuncs.com',
    'bucket' => 'your bucket',
];
$oss = new \PFinal\Storage\AliOss($config);

$bool = $oss->put('test.jpg', file_get_contents('/Users/ethan/Pictures/1.jpg'));
var_dump($bool);

//原图
echo $oss->url('test.jpg');

echo '<br>';

//图片处理 规则名称: "s"
echo $oss->url('test.jpg', 's');

