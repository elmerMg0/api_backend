<?php

namespace app\controllers;
use Yii;
use \app\models\Cliente;
use Exception;

class ClienteController extends \yii\web\Controller
{
    public function actionIndex()
    {
        return $this->render('index');
    }

    public function behaviors(){
        $behaviors = parent::behaviors();
        $behaviors["verbs"] = [
            "class" => \yii\filters\VerbFilter::class,
            "actions" => [
                'index' => [ 'get'],
                'create' => [ 'post' ],
                'update' => [ 'put' ]
            ]
        ];

        return $behaviors;

    }

    public function beforeAction( $action ){
        if (Yii::$app->getRequest()->getMethod() === 'OPTIONS') {         	
            Yii::$app->getResponse()->getHeaders()->set('Allow', 'POST GET PUT');         	
            Yii::$app->end();     	
        }   

        $this->enableCsrfValidation = false;
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return parent::beforeAction($action);
    }

    public function actionCreate(){
        $params = Yii::$app->getRequest()->getBodyParams();
        $cliente = new Cliente();
        $cliente -> load($params,"");
        $cliente -> fecha_creacion = Date("H-m-d H:i:s");
        try{
            if($cliente->save()){
                //todo ok
                $response = [
                    "success" => true,
                    "message" => "Cliente agreado exitosamente",
                    'cliente' => $cliente
                ];
            }else{
                //Cuando hay error en los tipos de datos ingresados 
                $response = [
                    "success" => false,
                    "message" => "Existen parametros incorrectos",
                    'errors' => $cliente->errors
                ];
            }
        }catch(Exception $e){
        //cuando no se definen bien las reglas en el modelo ocurre este error, por ejemplo required no esta en modelo y en la base de datos si, 
        //existe incosistencia
            $response = [
                "success" => false,
                "message" => "ocurrio un error",
                'errors' => $e
            ];
        }

     
        return $response;

    }
}
