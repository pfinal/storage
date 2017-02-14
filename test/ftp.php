<?php

require_once __DIR__ . '/../vendor/autoload.php';

$config = [
    'host' => 'www.test.com',
    'username' => 'ftpuser',
    'password' => '123456',

    'port' => 21,
    'passive' => true,
    'timeout' => 30,

    'baseUrl' => 'http://static.it266.com/',
];

$ftp = new \PFinal\Storage\Ftp($config);
$bool = $ftp->put('test/abc.jpg', file_get_contents('/Users/ethan/Pictures/1.jpg'));
var_dump($bool);
var_dump($ftp->url('test/abc.jpg'));
var_dump($ftp->url('test/abc.jpg', 'm'));
