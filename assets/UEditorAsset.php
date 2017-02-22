<?php
namespace dungang\ueditor\assets;


use yii\web\AssetBundle;

class UEditorAsset extends AssetBundle
{
    public $sourcePath = __DIR__ . "/ueditor";
    public $js = [
        'ueditor.config.js',
        'ueditor.all.js',
    ];
}