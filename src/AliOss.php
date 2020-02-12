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
    protected $cdn; // static.example.com

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

        //$scheme = isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off' ? 'https' : 'http';
        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
            $scheme = strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']);
        } else {
            $scheme = isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off' ? 'https' : 'http';
        }

        if ($this->cdn != null) {
            return $scheme . '://' . $this->cdn . '/' . $key . $append;
        }

        return $scheme . '://' . $this->bucket . '.' . $this->endPoint . '/' . $key . $append;
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


    // $callbackUrl测试时，未收到aliyun的回调
    // https://help.aliyun.com/document_detail/31988.html
    public function getClientToken($dir = 'uploads/', $callbackUrl = '')
    {
        $id = $this->accessKey;
        $key = $this->secret;
        // $host的格式为 bucketname.endpoint，请替换为您的真实信息。
        $host = '//' . $this->bucket . '.' . $this->endPoint;// 'http://bucket-name.oss-cn-hangzhou.aliyuncs.com';
        // $callbackUrl为上传回调服务器的URL，请将下面的IP和Port配置为您自己的真实URL信息。
        // $callbackUrl = 'http://88.88.88.88:8888/aliyun-oss-appserver-php/php/callback.php';
        // $dir = 'user-dir-prefix/';          // 用户上传文件时指定的前缀。

        $callback_param = array('callbackUrl' => $callbackUrl,
            'callbackBody' => 'filename=${object}&size=${size}&mimeType=${mimeType}&height=${imageInfo.height}&width=${imageInfo.width}',
            'callbackBodyType' => "application/x-www-form-urlencoded");
        $callback_string = json_encode($callback_param);

        $base64_callback_body = base64_encode($callback_string);
        $now = time();
        $expire = 30;  //设置该policy超时时间是10s. 即这个policy过了这个有效时间，将不能访问。
        $end = $now + $expire;
        $expiration = self::gmt_iso8601($end);

        //最大文件大小.用户可以自己设置
        $condition = array(0 => 'content-length-range', 1 => 0, 2 => 1048576000);
        $conditions[] = $condition;

        // 表示用户上传的数据，必须是以$dir开始，不然上传会失败，这一步不是必须项，只是为了安全起见，防止用户通过policy上传到别人的目录。
        $start = array(0 => 'starts-with', 1 => '$key', 2 => $dir);
        $conditions[] = $start;


        $arr = array('expiration' => $expiration, 'conditions' => $conditions);
        $policy = json_encode($arr);
        $base64_policy = base64_encode($policy);
        $string_to_sign = $base64_policy;
        $signature = base64_encode(hash_hmac('sha1', $string_to_sign, $key, true));

        $response = array();
        $response['accessId'] = $id;
        $response['host'] = $host;
        $response['policy'] = $base64_policy;
        $response['signature'] = $signature;
        $response['expire'] = $end;
        $response['callback'] = $base64_callback_body;
        $response['dir'] = $dir;  // 这个参数是设置用户上传文件时指定的前缀。
        return $response;
    }

    public function gmt_iso8601($time)
    {
        $dtStr = date("c", $time);
        $mydatetime = new \DateTime($dtStr);
        $expiration = $mydatetime->format(\DateTime::ISO8601);
        $pos = strpos($expiration, '+');
        $expiration = substr($expiration, 0, $pos);
        return $expiration . "Z";
    }

}