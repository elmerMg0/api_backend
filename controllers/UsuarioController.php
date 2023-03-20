<?php

namespace app\controllers;

use app\models\Usuario;
use GuzzleHttp\Psr7\Response;
use PhpParser\Node\Stmt\Catch_;
use Yii;
use Exception;
class UsuarioController extends \yii\web\Controller
{
    public function behaviors(){
        $behaviors = parent::behaviors();
        $behaviors['verbs'] = [
            'class' => \yii\filters\VerbFilter::class,
            'actions' => [
                'login' => [ 'POST' ],
                'create-user'=>['POST'],
                'edit-user'=>['PUT']

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

   
    public function actionCreateUser(){
        $params = Yii::$app->getRequest()->getBodyParams();
        try{
            $usuario = new Usuario();
            $usuario->nombres = $params["nombres"];
            $usuario->username = $params["username"];
            $usuario->password_hash = Yii::$app->getSecurity()->generatePasswordHash($params["password"]);
            $usuario->access_token = Yii::$app->security->generateRandomString();

            if($usuario->save()){
                Yii::$app->getResponse()->getStatusCode(201);
                $response = [
                    'success'=> true,
                    'message'=> 'registered user',
                    'usuario'=>$usuario
                ];
                
            }else{
                Yii::$app->getResponse()->setStatusCode(422,'Data Validation Failed.');
                $response = [
                    'success' => false,
                    'message' => 'Wrong parameters',
                    'usuario' => $usuario->errors,
                ];
            }
        } catch(Exception $e){
            Yii::$app->getResponse()->getStatusCode();
            $response = [
                'success'=>true,
                'message'=> 'Error Registering User',
                'errors'=> $e->getMessage()
            ];
        }
        
        return $response;
    }
    public function actionDeleteUser($id){
        $params= Usuario::findOne($id);
        if($params){
            try{
                $params->delete();
                $response = [
                    'success'=>true,
                    'message'=>'User deleted'
                ];
            }catch(Exception $e){
                Yii::$app->getResponse()->getStatusCode(409);
                $response = [
                    'success'=> false,
                    'message'=>'Elimination failed',
                    'code'=>$e->getCode()
                ];
            }catch(Exception $e){
                Yii::$app->getResponse()->setStatusCode(422,'Data validation failed');
                $resultado = [
                    'success' => false,
                    'message'=>$e->getMessage(),
                    'code' => $e->getCode()
                ];
        }
        }else{
            Yii::$app->getResponse()->getStatusCode(404);
            $resultado = [
                'success' => false,
                'message' => 'user not found',
                
            ];
        }
        return $response;
    }
    public function actionEditUser($id){
        $params = Yii::$app->getRequest()->getBodyParams();
        $usuario = Usuario::findOne($id);
        if ($usuario) {
            $usuario->load($params, '');
            try{
                if ($usuario->save()) {
                    $response = [
                        'success' => true,
                        'message' => 'correct update',
                        'data' => $usuario
                    ];
                } else {
                    Yii::$app->getResponse()->setStatusCode(422, 'Data Validation Failed.');
                    $response = [
                        'success' => false,
                        'message' => 'failed update',
                        'data' => $usuario->errors
                    ];
                }
            }catch(Exception $e){
                $response = [
                    'success' => false,
                    'message' => 'Failed to update',
                    'data' => $e->getMessage()
                ];
            }
            
            
        }else{
            Yii::$app->getResponse()->getStatusCode(404);
            $response = [
                'success' => false,
                'message' => 'User not found',
                
            ];
        }

        return $response;
    }
    public function actionIndex(){
        $users = Usuario::find()->all();
        $response = [
            "success" => true,
            "message" => "Acción realizada con éxito",
            "users" => $users
        ];
        return $response;
    }

}
