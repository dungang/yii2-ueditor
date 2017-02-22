<?php
/**
 * Created by PhpStorm.
 * User: dungang@126.com
 * Date: 2017/1/20
 * Time: 18:13
 */

namespace dungang\ueditor\widgets;

use dungang\ueditor\assets\UEditorAsset;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\InputWidget;

class Editor  extends InputWidget
{
    //配置选项，参阅Ueditor官网文档(定制菜单等)
    public $clientOptions = [];

    public $toolBars = [];

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        $this->id = $this->hasModel() ? Html::getInputId($this->model, $this->attribute) : $this->id;
        $options = [
            'serverUrl' => Url::to(['/ueditor/upload']),
            'initialFrameWidth' => '100%',
            'initialFrameHeight' => '400',
            'lang' => (strtolower(Yii::$app->language) == 'en-us') ? 'en' : 'zh-cn',
        ];
        $baseBars = [
            'fullscreen', 'source', 'undo', 'redo', '|',
            'fontsize',
            'bold', 'italic', 'underline', 'fontborder', 'strikethrough', 'removeformat',
            'formatmatch', 'autotypeset', 'blockquote', 'pasteplain', '|',
            'forecolor', 'backcolor', '|',
            'lineheight', '|',
            'indent', '|',
        ];
         $this->clientOptions['toolbars']=[array_merge($baseBars,$this->toolBars)];

        $this->clientOptions = ArrayHelper::merge($options, $this->clientOptions);
        parent::init();
    }

    public function run()
    {
        $this->registerClientScript();
        if ($this->hasModel()) {
            return Html::activeTextarea($this->model, $this->attribute, ['id' => $this->id]);
        } else {
            return Html::textarea($this->id, $this->value, ['id' => $this->id]);
        }
    }

    /**
     * 注册客户端脚本
     */
    protected function registerClientScript()
    {
        UEditorAsset::register($this->view);
        $clientOptions = Json::encode($this->clientOptions);
        $script = "UE.getEditor('" . $this->id . "', " . $clientOptions . ")";
        $this->view->registerJs($script, View::POS_READY);
    }
}