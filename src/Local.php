<?php

namespace PFinal\Storage;

class Local
{
    protected $basePath;
    protected $baseUrl;

    public function __construct(array $config = array())
    {
        foreach ($config as $key => $item) {
            $this->$key = $item;
        }
    }

    /**
     * 保存文件
     *
     * @param string $key
     * @param $data
     * @return bool
     */
    public function put($key, $data)
    {
        $size = file_put_contents($this->getFullName($key), $data, LOCK_EX);
        return $size !== false;
    }

    /**
     * 返回文件外链
     * @param $key
     * @param string|null $rule
     * @return string
     */
    public function url($key, $rule = null)
    {
        //todo 处理rule

        return $this->baseUrl . $key;
    }

    /**
     * 完整文件名
     * @param $key
     * @return string
     */
    protected function getFullName($key)
    {
        //todo 验证不能到basePath的上级

        return rtrim($this->basePath, '/\\') . DIRECTORY_SEPARATOR . $key;
    }
}