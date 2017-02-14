<?php

require_once __DIR__ . '/../vendor/autoload.php';

$config = array(
    'accessKey' => 'TOeV-fwwxsssf3s_45tCziKjRD9-bPyXUKjbuX7b',
    'secretKey' => 'pbHrgwwwp_wpClxeeGrYKLNdEhLd02Jrew3t5h',
    'bucketName' => 'test',
    'baseUrl' => 'http://static.pfinal.cn/',
    'separator' => '!',
);
$qiniu = new \PFinal\Storage\Qiniu($config);
$bool = $qiniu->put('test/abc.jpg', file_get_contents('/Users/ethan/Pictures/1.jpg'));
var_dump($bool);
var_dump($qiniu->url('test/abc.jpg'));
var_dump($qiniu->url('test/abc.jpg', 'm'));
