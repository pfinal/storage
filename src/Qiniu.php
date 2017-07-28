<?php

namespace PFinal\Storage;

use Qiniu\Storage\BucketManager;
use Qiniu\Storage\UploadManager;
use Qiniu\Auth;

class Qiniu implements StorageInterface
{
    protected $accessKey;
    protected $secretKey;
    protected $bucketName;
    protected $baseUrl;
    protected $separator = '-';
    protected $error;

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
            $this->error = $error->message();
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

    /**
     * 移动文件
     *
     * @param $key
     * @param $newKey
     * @return bool
     */
    public function rename($key, $newKey)
    {
        //初始化Auth状态：
        $auth = new Auth($this->accessKey, $this->secretKey);

        //初始化BucketManager
        $bucketMgr = new BucketManager($auth);

        //确保这个key在你空间中存在
        $bucket = $this->bucketName;

        //将文件从文件$key 改成文件名$newKey 可以在不同bucket移动
        $err = $bucketMgr->move($bucket, $key, $bucket, $newKey);

        if ($err !== null) {
            $this->error = $err->message();
            return false;
        } else {
            return true;
        }
    }

    /**
     * 删除文件
     *
     * @param $key
     * @return bool
     */
    public function delete($key)
    {
        //初始化Auth状态：
        $auth = new Auth($this->accessKey, $this->secretKey);

        //初始化BucketManager
        $bucketMgr = new BucketManager($auth);

        //确保这个key在你空间中存在
        $bucket = $this->bucketName;

        $err = $bucketMgr->delete($bucket, $key);

        if ($err !== null) {
            $this->error = $err->message();
            return false;
        } else {
            return true;
        }
    }

    /**
     * 错误消息
     *
     * @return string
     */
    public function error()
    {
        return $this->error;
    }
}