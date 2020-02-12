<?php

namespace PFinal\Storage;

class Local implements StorageInterface
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
        $filename = $this->getFullName($key);
        if (!file_exists(dirname($filename))) {
            mkdir(dirname($filename), 0777, true);
        }
        $size = file_put_contents($filename, $data, LOCK_EX);
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
        if ($rule != null) {
            $key = $this->getThumbKey($key, $rule);
        }

        return $this->baseUrl . $key;
    }

    /**
     * 重命名
     *
     * @param $key
     * @param $newKey
     * @return bool
     */
    public function rename($key, $newKey)
    {
        $newFile = $this->getFullName($newKey);
        if (!file_exists(dirname($newFile))) {
            mkdir(dirname($newFile), 0777, true);
        }

        return rename($this->getFullName($key), $newFile);
    }

    /**
     * 复制
     *
     * @param $key
     * @param $newKey
     * @return bool
     */
    public function copy($key, $newKey)
    {
        $newFile = $this->getFullName($newKey);
        if (!file_exists(dirname($newFile))) {
            mkdir(dirname($newFile), 0777, true);
        }

        return copy($this->getFullName($key), $newFile);
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

    /**
     * 删除文件
     *
     * @param $key
     * @return bool
     */
    public function delete($key)
    {
        if (!file_exists($this->getFullName($key))) {
            return true;
        }
        return unlink($this->getFullName($key));
    }

    /**
     * 错误消息
     *
     * @return string
     */
    public function error()
    {
        // TODO: Implement error() method.
    }

    /**
     * @param string $key "uploads/201809/02/xxx.jpg"
     * @param string $rule "s"
     * @return string "uploads/thumb/s/201809/02/xxx.jpg"
     */
    public function getThumbKey($key, $rule)
    {
        return preg_replace('/^uploads\//', 'uploads/thumb/' . $rule . '/', $key);
    }

    /**
     * 生成缩略图文件
     * @param string $key 最前面不要加斜线 示例: "uploads/201809/02/xxx.jpg"
     * @param string $rule 需要生成的规格 示例: "s"
     * @param array $allowRules 允许缩略图规格列表
     * @return \Leaf\Response|\Symfony\Component\HttpFoundation\StreamedResponse
     *
     * //访问时动态生成缩略图
     * //http://example.com/uploads/201703/07/2.jpg
     * //http://example.com/uploads/thumb/m/201703/07/2.jpg
     * Route::any('uploads/thumb/:rule/:month/:day/:file.:ext', function (\Leaf\Application $app, $month, $day, $rule, $file, $ext) {
     *      $fullFileName = 'uploads/' . $month . '/' . $day . '/' . $file . '.' . $ext;
     *      return $app['storage']->thumb($fullFileName, $rule);
     * });
     */
    public function thumb($key, $rule, $allowRules = null)
    {
        if (empty($allowRules)) {
            $allowRules = array(
                's' => array('w' => 120, 'h' => 120, 'cut' => true),
                'm' => array('w' => 400, 'h' => 400,),
                'l' => array('w' => 800, 'h' => 800,),
            );
        }

        if (!array_key_exists($rule, $allowRules)) {
            return new \Leaf\Response('404 Not Found', 404);
        }

        $fullFileName = $this->getFullName($key);

        $thumbnailKey = $this->getThumbKey($key, $rule);
        $newFileName = $this->getFullName($thumbnailKey);

        if (!file_exists($fullFileName)) {
            return new \Leaf\Response('404 Not Found', 404);
        }

        if (isset($allowRules[$rule]['cut']) && $allowRules[$rule]['cut']) {
            \Leaf\Image::thumbCut($fullFileName, $newFileName, $allowRules[$rule]['w'], $allowRules[$rule]['h']);
        } else {
            \Leaf\Image::resize($fullFileName, $newFileName, $allowRules[$rule]['w'], $allowRules[$rule]['h']);
        }

        $ext = ltrim(strtolower(strrchr($thumbnailKey, '.')), '.');

        //$header = array('Content-type' => 'image/png');
        $header = array('Content-type' => 'image/' . $ext);

        return new \Symfony\Component\HttpFoundation\StreamedResponse(function () use ($newFileName) {
            readfile($newFileName);
        }, 200, $header);
    }



}