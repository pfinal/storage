<?php

namespace PFinal\Storage;

use Qiniu\Storage\UploadManager;
use Qiniu\Auth;

class Qiniu
{
    protected $accessKey;
    protected $secretKey;
    protected $bucketName;
    protected $baseUrl;
    protected $separator = '-';

    public function __construct(array $config = array())
    {
        foreach ($config as $key => $item) {
            $this->$key = $item;
        }
    }

    /**
     * 上传文件
     *
     * @param string $key
     * @param $data
     * @return bool
     */
    public function put($key, $data)
    {
        $upManager = new UploadManager();
        $auth = new Auth($this->accessKey, $this->secretKey);
        $token = $auth->uploadToken($this->bucketName);
        list($ret, $error) = $upManager->put($token, $key, $data);

        //失败情况下ret为null
        if ($ret == null) {
            //echo $error->message();
            return false;
        } else {
            //echo $ret['hash'];  //FizFMFnR5n7w8DvaFDQ4__RRXnJV
            //echo $ret['key']; //test/bmw.jpeg
            return true;
        }
    }

    /**
     * 返回文件外链
     *
     * @param $key
     * @param string|null $rule 处理规则，为null时原样返回
     * @return string
     */
    public function url($key, $rule = null)
    {
        if ($rule !== null) {
            $key = $key . $this->separator . $rule;
        }
        return $this->baseUrl . $key;
    }
}