<?php

namespace PFinal\Storage;

//composer pfinal/http
use PFinal\Http\Client;

class MyHttp implements StorageInterface
{
    protected $error;
    protected $accessKey;
    protected $secretKey;

    protected $baseUrl;

    //服务端参考 MyHttpTrait.php
    protected $api;


    public function __construct(array $config = array())
    {
        foreach ($config as $key => $item) {
            $this->$key = $item;
        }
    }

    public function appendSign($data)
    {
        $timestamp = (string)time();
        $nonce = (string)rand(1000000, 9999999);
        $sign = md5($this->accessKey . $timestamp . $nonce . $this->secretKey);

        return array_merge($data, array(
            'accessKey' => $this->accessKey,
            'timestamp' => $timestamp,
            'nonce' => $nonce,
            'sign' => $sign,
        ));
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
        $tempFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid(time(), true);
        file_put_contents($tempFile, $data);

        $client = new Client();
        $res = $client->file($this->api, 'file', $tempFile, $this->appendSign(array('filename' => $key)));

        unlink($tempFile);

        $this->error = $res->getBody();

        if ($res->getStatusCode() == 200) {
            $arr = @json_decode($res->getBody(), true);
            return is_array($arr) && array_key_exists('status', $arr) && $arr['status'];
        }

        return false;
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
        if ($rule) {
            $key = dirname($key) . '/' . $rule . '/' . basename($key);
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
        $client = new Client();

        $res = $client->post($this->api, $this->appendSign(array(
            'action' => 'move',
            'key' => $key,
            'newKey' => $key,
        )));

        $this->error = $res->getBody();

        if ($res->getStatusCode() == 200) {
            $arr = @json_decode($res->getBody(), true);
            return is_array($arr) && array_key_exists('status', $arr) && $arr['status'];
        }

        return false;
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
        $client = new Client();

        $res = $client->post($this->api, $this->appendSign(array(
            'action' => 'copy',
            'key' => $key,
            'newKey' => $key,
        )));

        $this->error = $res->getBody();

        if ($res->getStatusCode() == 200) {
            $arr = @json_decode($res->getBody(), true);
            return is_array($arr) && array_key_exists('status', $arr) && $arr['status'];
        }

        return false;

    }


    /**
     * 删除文件
     *
     * @param $key
     * @return bool
     */
    public function delete($key)
    {
        $client = new Client();

        $res = $client->post($this->api, $this->appendSign(array(
            'action' => 'delete',
            'key' => $key,
        )));

        $this->error = $res->getBody();

        if ($res->getStatusCode() == 200) {
            $arr = @json_decode($res->getBody(), true);
            return is_array($arr) && array_key_exists('status', $arr) && $arr['status'];
        }

        return false;
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

