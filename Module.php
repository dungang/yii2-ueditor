<?php

namespace dungang\ueditor;

/**
 * ueditor module definition class
 */
class Module extends \yii\base\Module
{
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'dungang\ueditor\controllers';


    /**
     * @var array ueditor config file
     */
    public $config =  [];

    /**
     * @inheritdoc
     */
    public function init()
    {
        $basePath = \Yii::$app->request->getBaseUrl() . '/editor';
        $config = [
            "imageUrlPrefix"=> $basePath,//图片访问路径前缀
            "imagePathFormat" => "/upload/image/{yyyy}{mm}{dd}/{time}{rand:6}", //上传保存路径
            "imageManagerUrlPrefix"=> $basePath,//图片管理路径前缀
            "fileManagerUrlPrefix"=> $basePath,//文件管理路径前缀
        ];
        $this->config = array_merge($config,$this->config);
        parent::init();
    }
}
