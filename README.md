文件存储

> 安装

```
composer require pfinal/storage
```

* 本地存储 Local
    
* 7牛存诸 Qiniu 

    请先 composer require qiniu/php-sdk
    
```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

$config = array(
    'accessKey' => 'TOeV-fwwxsssf3s_45tCziKjRD9-bPyXUKjbuX7b',
    'secretKey' => 'pbHrgwwwp_wpClxeeGrYKLNdEhLd02Jrew3t5h',
    'bucketName' => 'test',
    'baseUrl' => 'http://static.pfinal.cn/',
    'separator' => '-',
);
$qiniu = new \PFinal\Storage\Qiniu($config);
$bool = $qiniu->put('test/1.jpg', file_get_contents('/Users/ethan/Pictures/1.jpg'));
var_dump($bool);
var_dump($qiniu->url('test/1.jpg'));
var_dump($qiniu->url('test/1.jpg', 'm'));
```