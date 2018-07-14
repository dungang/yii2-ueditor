<?php
namespace dungang\ueditor\actions;

use Yii;
use yii\base\Action;
use yii\helpers\ArrayHelper;
use dungang\storage\StorageAction;
use dungang\storage\ChunkResponse;

class UEditorAction extends StorageAction
{

    /**
     *
     * @var array
     */
    public $config = [];

    public function init()
    {
        parent::init();
        // close csrf
        Yii::$app->request->enableCsrfValidation = false;
        // 默认设置
        $_config = require (__DIR__ . '/../config/config.php');
        // load config file
        $this->config = ArrayHelper::merge($_config, $this->config);
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
     *
     * @return string
     */
    protected function actionUpload()
    {
        switch (htmlspecialchars($_GET['action'])) {
            case 'uploadimage':
                $this->driver->uploadDir .= DIRECTORY_SEPARATOR . 'image';
                $this->driver->maxFileSize = $this->config['imageMaxSize'];
                $this->driver->accept = $this->reAccept($this->config['imageAllowFiles']);
                $this->driver->fileName = $this->config['imageFieldName'];
                break;
            case 'uploadvideo':
                $this->driver->uploadDir .= DIRECTORY_SEPARATOR . 'video';
                $this->driver->maxFileSize = $this->config['videoMaxSize'];
                $this->driver->accept = $this->reAccept($this->config['videoAllowFiles']);
                $this->driver->fileName = $this->config['videoFieldName'];
                break;
            default:
                $this->driver->uploadDir .= DIRECTORY_SEPARATOR . 'file';
                $this->driver->maxFileSize = $this->config['fileMaxSize'];
                $this->driver->accept = $this->reAccept($this->config['fileAllowFiles']);
                $this->driver->fileName = $this->config['fileFieldName'];
        }
        $rst = $this->driver->chunkUpload();
        return $this->response($rst);
    }

    /**
     * 获取已上传的文件列表
     *
     * @return string
     */
    protected function actionList()
    {
        /* 判断类型 */
        switch ($_GET['action']) {
            /* 列出文件 */
            case 'listfile':
                $this->driver->uploadDir .= DIRECTORY_SEPARATOR . 'file';
                $this->driver->accept = $this->reAccept($this->config['fileManagerAllowFiles']);
                break;
            /* 列出图片 */
            default:
                $this->driver->uploadDir .= DIRECTORY_SEPARATOR . 'image';
                $this->driver->accept = $this->reAccept($this->config['imageManagerAllowFiles']);
        }

        /* 获取参数 */
        $size = isset($_GET['size']) ? htmlspecialchars($_GET['size']) : 10;
        $start = isset($_GET['start']) ? htmlspecialchars($_GET['start']) : 0;
        $response = $this->driver->listFiles($start, $size);

        $result = [];
        $result['state'] = 'SUCCESS';
        $result['list'] = array_map(function ($val) {
            return [
                'url' => $this->formatListItemUrl($val)
            ];
        },$response->list);
        return json_encode($result);
    }

    /**
     * 获取当前上传成功文件的各项信息
     *
     * @param ChunkResponse $rst
     * @return array
     */
    public function response($rst)
    {
        $result = [];
        if ($rst->isCompleted) {
            $result['url'] = $this->formatUrl($rst->key);
            $result['title'] = $rst->name;
            $result['original'] = $rst->originName;
            $result['type'] = '.' . $rst->extension;
            $result['size'] = $rst->size;
        }
        if ($rst->isOk) {
            $result['state'] = 'SUCCESS';
        } else {
            $result['state'] = $rst['error'];
        }

        return json_encode($result);
    }

    protected function formatUrl($url)
    {
        if (strtolower(substr($url, 0, 4)) == 'http' || substr($url, 0, 2) == '//') {
            return $url;
        } else {
            return Yii::$app->request->baseUrl . '/' . $url;
        }
    }

    protected function formatListItemUrl($val)
    {
        if (isset($val['url'])) {
            return $val['url'];
        } else if (isset($val['object'])) {
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