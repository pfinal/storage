文件存储

> 安装

```
composer require pfinal/storage
```

* 本地存储 Local

* 阿里云 AliOss

    请先 composer require aliyuncs/oss-sdk-php
    
* 7牛存诸 Qiniu 

    请先 composer require qiniu/php-sdk
    
* Ftp
    
    请先 composer league/flysystem


> 提供接口

```
//上传文件
public function put($key, $data);
//获取url
public function url($key, $rule = null);
//重命名
public function rename($key, $newKey);
//删除
public function delete($key);
//获取错误消息
public function error();
```

> 使用示例

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

//7牛
$config = array(
    'accessKey' => 'TOeV-fwwxsssf3s_45tCziKjRD9-bPyXUKjbuX7b',
    'secretKey' => 'pbHrgwwwp_wpClxeeGrYKLNdEhLd02Jrew3t5h',
    'bucketName' => 'test',
    'baseUrl' => 'http://static.pfinal.cn/',
    'separator' => '-',
);
$qiniu = new \PFinal\Storage\Qiniu($config);
$bool = $qiniu->put('test/1.jpg', file_get_contents('/Users/ethan/Pictures/1.jpg'));

//原图url
var_dump($qiniu->url('test/1.jpg'));

//小图url 规则: "m"
var_dump($qiniu->url('test/1.jpg', 'm'));



//阿里云OSS

$config = [
    'accessKey' => 'your key',
    'secret' => 'your secret',
    'endPoint' => 'oss-cn-shanghai.aliyuncs.com',
    'bucket' => 'your bucket',
];
$oss = new \PFinal\Storage\AliOss($config);

$bool = $oss->put('test.jpg', file_get_contents('/Users/ethan/Pictures/1.jpg'));

//原图url
echo $oss->url('test.jpg');

//小图url 规则名称: "s"
echo $oss->url('test.jpg', 's');

```
