<?php

namespace PFinal\Storage;

use League\Flysystem\Adapter\Ftp as BaseFtp;
use League\Flysystem\Filesystem;

class Ftp implements StorageInterface
{
    protected $host;
    protected $username;
    protected $password;

    protected $port = 21;
    protected $passive = true;
    protected $timeout = 30;

    protected $baseUrl;

    public function __construct(array $config = array())
    {
        foreach ($config as $key => $item) {
            $this->$key = $item;
        }
    }

    /**
     * 上传
     *
     * @param string $key
     * @param $data
     * @return bool
     */
    public function put($key, $data)
    {
        $config = [
            'host' => $this->host,
            'username' => $this->username,
            'password' => $this->password,

            'port' => $this->port,
            'passive' => $this->passive,
            'timeout' => $this->timeout,
        ];
        $adapter = new BaseFtp($config);
        $fs = new Filesystem($adapter);

        $bool = @$fs->put($key, $data);
        return $bool;
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
        //todo 支持rule

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
        $config = [
            'host' => $this->host,
            'username' => $this->username,
            'password' => $this->password,

            'port' => $this->port,
            'passive' => $this->passive,
            'timeout' => $this->timeout,
        ];
        $adapter = new BaseFtp($config);
        $fs = new Filesystem($adapter);

        $bool = @$fs->rename($key, $newKey);
        return $bool;
    }
}