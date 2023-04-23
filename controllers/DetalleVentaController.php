<?php

namespace app\controllers;

use app\models\DetalleVenta;
use Yii;
class DetalleVentaController extends \yii\web\Controller
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors["verbs"] = [
            "class" => \yii\filters\VerbFilter::class,
            "actions" => [
                'index' => ['get'],
                'create' => ['post'],
                'update' => ['put', 'post'],
                'delete' => ['delete'],
                'get-product' => ['get'],

            ]
        ];
        $behaviors['authenticator'] = [         	
            'class' => \yii\filters\auth\HttpBearerAuth::class,         	
            'except' => ['options']     	
        ];
        return $behaviors;
    }

    public function beforeAction($action)
    {
        if (Yii::$app->getRequest()->getMethod() === 'OPTIONS') {
            Yii::$app->getResponse()->getHeaders()->set('Allow', 'POST GET PUT');
            Yii::$app->end();
        }

        $this->enableCsrfValidation = false;
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return parent::beforeAction($action);
    }

    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionGetBestSellerProduct(){
        $detail = DetalleVenta::find()
                    ->select(['sum(cantidad) as cantidad', 'producto.nombre' ])
                    ->join('LEFT JOIN', 'producto', 'producto.id=detalle_venta.producto_id')
                    ->groupBy(['producto_id', 'producto.nombre' ])
                    ->orderBy(['cantidad' => SORT_DESC])
                    ->asArray()
                    ->limit(5)
                    ->all();
        if($detail){
            $response = [
                'success' => true,
                'message' => 'Productos mas vendidos',
                'list' => $detail
            ];
        }else{
            $response = [
                'success' => false,
                'message' => 'no existen ventas aun!',
                'list' => $detail
            ];
        }
        return $response;
    }

}
