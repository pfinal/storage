<?php

namespace PFinal\Storage;

interface StorageInterface
{
    /**
     * 上传文件
     *
     * @param string $key
     * @param $data
     * @return bool
     */
    public function put($key, $data);

    /**
     * 返回文件外链
     *
     * @param $key
     * @param string|null $rule 处理规则，为null时原样返回
     * @return string
     */
    public function url($key, $rule = null);

    /**
     * 移动文件
     *
     * @param $key
     * @param $newKey
     * @return bool
     */
    public function rename($key, $newKey);
}