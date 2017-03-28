<?php
namespace dungang\ueditor\actions;

use Yii;
use yii\base\Action;
use yii\helpers\ArrayHelper;
use dungang\storage\ActionTrait;

class UEditorAction extends Action
{
    use ActionTrait;

    /**
     * @var array
     */
    public $config = [];

    public function init()
    {
        //close csrf
        Yii::$app->request->enableCsrfValidation = false;
        //默认设置
        $_config = require(__DIR__ . '/../config/config.php');
        //load config file
        $this->config = ArrayHelper::merge($_config, $this->config);

        parent::init();
    }

    /**
     * 处理action
     */
    public function run()
    {
        $action = Yii::$app->request->get('action');
        switch ($action) {
            case 'config':
                $result = json_encode($this->config);
                break;
            /* 上传图片 */
            case 'uploadimage':
                /* 上传视频 */
            case 'uploadvideo':
                /* 上传文件 */
            case 'uploadfile':
                $result = $this->actionUpload();
                break;
            /* 列出图片 */
            case 'listimage':
                /* 列出文件 */
            case 'listfile':
                $result = $this->actionList();
                break;
            default:
                $result = json_encode(array(
                    'state' => '请求地址出错'
                ));
                break;
        }
        /* 输出结果 */
        if (isset($_GET["callback"])) {
            if (preg_match("/^[\w_]+$/", $_GET["callback"])) {
                return htmlspecialchars($_GET["callback"]) . '(' . $result . ')';
            } else {
                return json_encode(array(
                    'state' => 'callback参数不合法'
                ));
            }
        } else {
            return $result;
        }
    }


    /**
     * 上传
     * @return string
     */
    protected function actionUpload()
    {
        $post = \Yii::$app->request->post();
        $this->instanceDriver($post);
        switch (htmlspecialchars($_GET['action'])) {
            case 'uploadimage':
                $this->driverInstance->saveDir .= DIRECTORY_SEPARATOR . 'image';
                $this->driverInstance->maxFileSize = $this->config['imageMaxSize'];
                $this->driverInstance->accept = $this->reAccept($this->config['imageAllowFiles']);
                $this->driverInstance->fieldName = $this->config['imageFieldName'];
                break;
            case 'uploadvideo':
                $this->driverInstance->saveDir .= DIRECTORY_SEPARATOR . 'video';
                $this->driverInstance->maxFileSize = $this->config['videoMaxSize'];
                $this->driverInstance->accept = $this->reAccept($this->config['videoAllowFiles']);
                $this->driverInstance->fieldName = $this->config['videoFieldName'];
                break;
            default:
                $this->driverInstance->saveDir .= DIRECTORY_SEPARATOR . 'file';
                $this->driverInstance->maxFileSize = $this->config['fileMaxSize'];
                $this->driverInstance->accept = $this->reAccept($this->config['fileAllowFiles']);
                $this->driverInstance->fieldName = $this->config['fileFieldName'];
        }
        $rst = $this->driverInstance->save();
        return $this->response($rst);
    }

    /**
     * 获取已上传的文件列表
     * @return string
     */
    protected function actionList()
    {
        $post = \Yii::$app->request->post();
        $this->instanceDriver($post);
        /* 判断类型 */
        switch ($_GET['action']) {
            /* 列出文件 */
            case 'listfile':
                $this->driverInstance->saveDir .= DIRECTORY_SEPARATOR . 'file';
                $this->driverInstance->accept = $this->reAccept($this->config['fileManagerAllowFiles']);
                break;
            /* 列出图片 */
            default:
                $this->driverInstance->saveDir .= DIRECTORY_SEPARATOR . 'image';
                $this->driverInstance->accept = $this->reAccept($this->config['imageManagerAllowFiles']);
        }

        /* 获取参数 */
        $size = isset($_GET['size']) ? htmlspecialchars($_GET['size']) : 10;
        $start = isset($_GET['start']) ? htmlspecialchars($_GET['start']) : 0;
        $result = $this->driverInstance->listFiles($start, $size);
        if ($result['code'] == 0) {
            $result['state'] = 'SUCCESS';
            $result['list'] = array_map(function ($val) {
                return [
                    'url'=>$this->formatListItemUrl($val)
                ];
            }, $result['list']);
        } else {
            $result['state'] = $result['message'];
        }
        return json_encode($result);
    }

    /**
     * 获取当前上传成功文件的各项信息
     * @return array
     */
    public function response($rst)
    {
        $result = [];
        if (isset($rst['object'])) {
            /* @var $object \dungang\storage\File */
            $object = $rst['object'];
            $result['url'] = $this->formatUrl($object->url);
            $result['title'] = $object->newName;
            $result['original'] = $object->name;
            $result['type'] = '.' . $object->extension;
            $result['size'] = $object->size;
        }
        if ($rst['code'] == 0) {
            $result['state'] = 'SUCCESS';
        } else {
            $result['state'] = $rst['message'];
        }

        return json_encode($result);
    }

    protected function formatUrl($url){
        if( strtolower(substr($url,0,4)) == 'http' || substr($url,0,2) == '//') {
            return $url;
        } else {
            return Yii::$app->request->baseUrl . '/' . $url;
        }
    }

    protected function formatListItemUrl($val)
    {
        if (isset($val['url'])) {
            return $val['url'];
        } else if( isset($val['object'])){
            return $this->formatUrl($val['object']);
        } else {
            return '';
        }
    }

    public function reAccept($allow)
    {
        return array_map(function ($val) {
            return ltrim($val, '.');
        }, $allow);
    }
}