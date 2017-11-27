<?php

namespace PFinal\Storage;

use OSS\Core\OssException;
use OSS\OssClient;

/**
 *
 * 阿里云OSS
 *
 * composer require "aliyuncs/oss-sdk-php": "^2.2"
 */
class AliOss implements StorageInterface
{
    protected $accessKey;  //Access Key ID
    protected $secret;     //Access Key Secret
    protected $endPoint;
    protected $bucket;
    //图片处理规则分隔符
    protected $separator = '?x-oss-process=style/';
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
     * @param mixed $data 数据 例如 $data = file_get_contents('/Users/ethan/Pictures/1.jpg');
     * @return bool
     */
    public function put($key, $data)
    {
        //初始化阿里oss
        $ossClient = new OssClient($this->accessKey, $this->secret, $this->endPoint);

        //上传
        try {
            $ossClient->putObject($this->bucket, $key, $data);
            return true;
        } catch (OssException $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }


    /**
     * 删除单个文件
     *
     * @param string $key
     * @return bool
     */
    public function delete($key)
    {
        $ossClient = new OssClient($this->accessKey, $this->secret, $this->endPoint);

        try {
            $ossClient->deleteObject($this->bucket, $key);
        } catch (OssException $e) {
            $this->error = $e->getMessage();
            return false;
        }

        return true;
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
        $append = '';
        if ($rule != null) {
            //http://kushu-test.oss-cn-shanghai.aliyuncs.com/haha.jpg?x-oss-process=style/s
            $append = $this->separator . $rule;
        }

        $http = isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off' ? 'https' : 'http';

        return $http . '://' . $this->bucket . '.' . $this->endPoint . '/' . $key . $append;
    }

    /**
     * 重命名文件
     *
     * @param $key
     * @param $newKey
     * @return bool
     */
    public function rename($key, $newKey)
    {
        $ossClient = new OssClient($this->accessKey, $this->secret, $this->endPoint);

        try {

            //todo 没看到有重命名名接口，暂时先这样处理

            $ossClient->copyObject($this->bucket, $key, $this->bucket, $newKey);
            $ossClient->deleteObject($this->bucket, $key);
        } catch (OssException $e) {
            $this->error = $e->getMessage();
            return false;
        }

        return true;
    }

    /**
     * 复制文件
     *
     * @param $key
     * @param $newKey
     * @return bool
     */
    public function copy($key, $newKey)
    {
        $ossClient = new OssClient($this->accessKey, $this->secret, $this->endPoint);

        try {
            $ossClient->copyObject($this->bucket, $key, $this->bucket, $newKey);
        } catch (OssException $e) {
            $this->error = $e->getMessage();
            return false;
        }

        return true;
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
