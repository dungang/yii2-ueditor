<?php
namespace dungang\ueditor\assets;


use yii\web\AssetBundle;

class UEditorAsset extends AssetBundle
{
 
    public $js = [
        'ueditor.config.js',
        'ueditor.all.js',
    ];
    
    public function init()
    {
    	$this->sourcePath = __DIR__ . "/ueditor";
    }
}