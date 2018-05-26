<?php

namespace PFinal\Storage;

use Leaf\Json;
use Leaf\Request;


///**
// * @Route file/upload
// */
//public function upload(Request $request)
//{
//    return $this->doUpload($request);
//}

trait MyHttpTrait
{
    protected function getBaseDir()
    {
        return 'temp';
    }

    //子类重写这个方法
    protected function checkSign(Request $request)
    {
        throw new \Exception('sign error');
//        $info = [
//            //$accessKey => $secretKey
//            '1001' => '289dff07669d7a23de0ef88d2f7129e7'
//        ];
//
//        $accessKey = $request->get('accessKey');
//        $timestamp = $request->get('timestamp');
//        $nonce = $request->get('nonce');
//
//        $sign = $request->get('sign');
//
//        if (!array_key_exists($accessKey, $info)) {
//            throw new \Exception('sign error');
//        }
//
//        //验证token
//        if ($sign !== $this->doSign($accessKey, $timestamp, $nonce, $info[$accessKey])) {
//            throw new \Exception('sign error');
//        }
    }

    protected function doSign($accessKey, $timestamp, $nonce, $secretKey)
    {
        //验证token
        return md5($accessKey . $timestamp . $nonce . $secretKey);
    }

    public function doUpload(Request $request)
    {
        $this->checkSign($request);

        $action = $request->get('action', 'upload');
        if ($action == 'upload') {
            return $this->_doUpload($request);
        }

        if ($action == 'move') {
            return $this->doMove($request);
        }

        if ($action == 'delete') {
            return $this->doDelete($request);
        }

        if ($action == 'copy') {
            return $this->doCopy($request);
        }

    }

    private function _doUpload(Request $request)
    {

        $filename = $request->get('filename');
        $this->checkFilename($filename);

        //文件存在则报错
        $fullName = rtrim($this->getBaseDir(), '/\\') . DIRECTORY_SEPARATOR . $filename;
        if (file_exists($fullName)) {
            return Json::renderWithFalse('file exists');
        }

        if (!file_exists(dirname($fullName))) {
            mkdir(dirname($fullName), 0777, true);
        }

        //判断是否在baseDir目录下
        if (stripos(realpath(dirname($fullName)), realpath($this->getBaseDir())) !== 0) {
            return Json::renderWithFalse('filename error');
        }

        $temp = $_FILES['file']['tmp_name'];
        if (move_uploaded_file($temp, $fullName)) {
            return Json::renderWithTrue();
        } else {
            return Json::renderWithFalse();
        }
    }

    private function checkFilename($filename)
    {
        // 必须限定在baseDir目录内，不能出现 ..
        // (\w+\/)* 目录可有可无
        // \w+ 文件名必须有
        // (\.\w+)*  扩展名可有可无
        if (!preg_match('/^(\w+\/)*\w+(\.\w+)*$/', $filename)) {
            throw new \Exception('filename error');
        }
    }

    private function doMove(Request $request)
    {
        $key = $request->get('key');
        $newFile = $request->get('newKey');

        $this->checkFilename($key);
        $this->checkFilename($newFile);

        $fullNameOld = rtrim($this->getBaseDir(), '/\\') . DIRECTORY_SEPARATOR . $key;
        if (!file_exists($fullNameOld)) {
            return Json::renderWithFalse('file not exists');
        }

        $fullNameNew = rtrim($this->getBaseDir(), '/\\') . DIRECTORY_SEPARATOR . $newFile;

        if (!file_exists(dirname($fullNameNew))) {
            mkdir(dirname($fullNameNew), 0777, true);
        }

        //判断是否在baseDir目录下
        if (stripos(realpath(dirname($fullNameNew)), realpath($this->getBaseDir())) !== 0) {
            return Json::renderWithFalse('filename error');
        }

        if (rename($fullNameOld, $fullNameNew)) {
            return Json::renderWithTrue();
        }
        return Json::renderWithFalse();
    }

    private function doCopy(Request $request)
    {
        $key = $request->get('key');
        $newFile = $request->get('newKey');

        $this->checkFilename($key);
        $this->checkFilename($newFile);

        $fullNameOld = rtrim($this->getBaseDir(), '/\\') . DIRECTORY_SEPARATOR . $key;
        if (!file_exists($fullNameOld)) {
            return Json::renderWithFalse('file not exists');
        }

        $fullNameNew = rtrim($this->getBaseDir(), '/\\') . DIRECTORY_SEPARATOR . $newFile;

        if (!file_exists(dirname($fullNameNew))) {
            mkdir(dirname($fullNameNew), 0777, true);
        }

        //判断是否在baseDir目录下
        if (stripos(realpath(dirname($fullNameNew)), realpath($this->getBaseDir())) !== 0) {
            return Json::renderWithFalse('filename error');
        }

        if (copy($fullNameOld, $fullNameNew)) {
            return Json::renderWithTrue();
        }
        return Json::renderWithFalse();
    }

    private function doDelete(Request $request)
    {
        $key = $request->get('key');
        $this->checkFilename($key);

        $fullName = rtrim($this->getBaseDir(), '/\\') . DIRECTORY_SEPARATOR . $key;

        if (!file_exists($fullName)) {
            return Json::renderWithFalse('file not exists');
        }

        //判断是否在baseDir目录下
        if (stripos(realpath(dirname($fullName)), realpath($this->getBaseDir())) !== 0) {
            return Json::renderWithFalse('filename error');
        }

        unlink($fullName);
    }
}