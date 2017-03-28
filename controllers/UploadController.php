<?php
/**
 * Created by PhpStorm.
 * User: dungang
 * Date: 2017/1/20
 * Time: 17:52
 */

namespace dungang\ueditor\controllers;


use yii\filters\AccessControl;
use yii\web\Controller;

class UploadController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access'=>[
                'class'=>AccessControl::className(),
                'rules'=>[
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ]
            ],
        ];
    }

    public function actions()
    {
        return [
            'index' => [
                'class' => 'dungang\ueditor\actions\UEditorAction',
            ]
        ];
    }
}