<?php

namespace app\controllers;

use app\models\Empresa;
use Faker\Provider\ar_EG\Company;
use Yii;
use Exception;

class EmpresaController extends \yii\web\Controller
{
    public function behaviors(){
        $behaviors = parent::behaviors();
        $behaviors['verbs'] = [
            'class' => \yii\filters\VerbFilter::class,
            'actions' => [
                'login' => [ 'POST' ],
                'create-user'=>['POST'],
                'update'=>['POST']

            ]
         ];
        return $behaviors;
    }

    public function beforeAction( $action ) {
        if (Yii::$app->getRequest()->getMethod() === 'OPTIONS') {         	
            Yii::$app->getResponse()->getHeaders()->set('Allow', 'POST GET PUT');         	
            Yii::$app->end();     	
        }     
        $this->enableCsrfValidation = false;
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return parent::beforeAction($action);
    }
    public function actionGetCompany(){
        $company = Empresa::find()->all();
        if($company){
            $response = [
                'success'=>true,
                'data'=>$company[0]
            ];
        }else{
            $response = [
                'success'=>false,
                'data'=>$company
            ];
        }
        return $response;
    }
    public function actionUpdate($id){
        $company = Empresa::findOne($id);
        $params = Yii::$app->getRequest()->getBodyParams();
        if($company){
            $company->load($params,'');
            try{
                if($company->save()){
                    $response = [
                        'success'=>true,
                        'message'=>'Se actualizo de manera correcta'
                    ];
                }else{
                    Yii::$app->getResponse()->setStatusCode(422, 'Data Validation Failed.');
                    $response = [
                        'success' => false,
                        'message' => 'Fallo al actualizar',
                        'data' => $company->errors
                    ];
                }
            }catch(Exception $e){
                $response = [
                    'success'=>false,
                    'message'=>'Error al Actualizar',
                    'data'=>$e->getMessage()
                ];
            }
        }else{
            Yii::$app->getResponse()->getStatusCode(404);
            $response = [
                'success'=>false,
                'message'=>'Company no encontrado'
            ];
        }
        return $response;
    }

    public function actionIndex()
    {
        return $this->render('index');
    }

}
