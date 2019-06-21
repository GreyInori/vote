<?php

namespace app\api\controller;


/**
 * @author Grey
 * 图片处理类
 */
trait Picture
{
    /**
     * 单张图片上传方法
     * @param string $filePath
     * @param string $requestPath
     * @return string
     */
    protected function toImgUp($filePath = '', $requestPath = '')
    {
        /* 加载上传的图片，并进行图片处理 */
        if ('' == $requestPath) {

            $requestPath = 'file';
        }

        $image = request()->file($requestPath);

        if (!is_object($image)) {

            $image = $image[$requestPath];
        }

        $data = $image->validate(['ext' => 'png,jpeg,jpg,gif,bmp'])->move(ROOT_PATH . 'public' . DS . 'static' . DS . 'images' . DS . $filePath . DS);

        if (!$data) {

            return self::returnMsg(500, 'fail', $image->getError());
        }
        /* 保存图片并获取图片完整路径 */
        $fileName = $data->getSaveName();

        $fileName = str_replace('\\','/',$fileName);
        // $file  = ROOT_PATH.'public'.DS.'static'.DS.'images'.DS.$filePath.DS.$fileName;
        /* 缩放图片并保存删除源文件，返回图片路径 */
        // $image = \think\Image::open(ROOT_PATH.'public'.DS.'static'.DS.'images'.DS.$filePath.DS.$fileName);

        // $del = unlink($file);

        // $image->save($file);

        $fileName = "/static/images/{$filePath}/{$fileName}";

        return $fileName;
    }

    /**
     * 图片批量上传方法
     * @param string $filePath
     * @param string $requestPath
     * @return array|bool
     */
    protected function toImgButchUp($filePath = '', $requestPath = '')
    {
        $fileArr = array();
        /* 加载上传的图片，并进行图片处理 */
        if ('' == $requestPath) {

            $requestPath = 'file';
        }

        $image = request()->file($requestPath);

        if (is_array($image)) {

            foreach ($image as $file) {

                $info = $file->validate(['ext' => 'png,jpeg,jpg,gif,bmp'])->move(ROOT_PATH . 'public' . DS . 'static' . DS . 'images' . DS . $filePath . DS);

                if ($info) {
                    // 成功上传后 给返回数组添加文件路径
                    $fileName = $info->getSaveName();
                    $fileName = str_replace('\\','/',$fileName);
                    array_push($fileArr, "/static/images/{$filePath}/{$fileName}");
                } else {
                    // 上传失败获取错误信息
                    return $file->getError();
                }
            }
        } else {

            $fileArr = true;
        }

        // $file  = ROOT_PATH.'public'.DS.'static'.DS.'images'.DS.$filePath.DS.$fileName;
        /* 缩放图片并保存删除源文件，返回图片路径 */
        // $image = \think\Image::open(ROOT_PATH.'public'.DS.'static'.DS.'images'.DS.$filePath.DS.$fileName);

        // $del = unlink($file);

        // $image->save($file);
        return $fileArr;
    }

    /**
     * 删除文件方法
     * @param  $path   string
     * @return $result boole
     */
    public function unlinkPic($path = '')
    {
        $path = ROOT_PATH . 'public' . $path;

        $data = unlink($path);

        if ($data) {

            return true;
        } else {

            return false;
        }
    }
}