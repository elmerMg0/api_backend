<?php

namespace app\controllers;

use app\models\Usuario;
use GuzzleHttp\Psr7\Response;
use PhpParser\Node\Stmt\Catch_;
use Yii;
use Exception;
use yii\data\Pagination;
use yii\helpers\Json;
use yii\web\UploadedFile;

class UsuarioController extends \yii\web\Controller
{
    public function behaviors(){
        $behaviors = parent::behaviors();
        $behaviors['verbs'] = [
            'class' => \yii\filters\VerbFilter::class,
            'actions' => [
                'login' => [ 'POST' ],
                'create-user'=>['POST'],
                'edit-user'=>['POST']

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
        //$params = Yii::$app->getRequest()->getBodyParams();
        $user = new Usuario();
        $file = UploadedFile::getInstanceByName('file');
        $data = Json::decode(Yii::$app->request->post('data'));

        // $data = Json::decode(Yii::$app->request->post('data'));
        if($file){
            $fileName = uniqid() . '.' . $file->getExtension();
            $file->saveAs(Yii::getAlias('@app/web/upload/') . $fileName);
            $user->url_image = $fileName;
        }
        try{
  
            $user->nombres = $data["nombres"];
            $user->username = $data["username"];
            $user->password_hash = Yii::$app->getSecurity()->generatePasswordHash($data["password"]);
            $user->access_token = Yii::$app->security->generateRandomString();
            $user->tipo = $data["tipo"];

            if($user->save()){
                Yii::$app->getResponse()->getStatusCode(201);
                $response = [
                    'success'=> true,
                    'message'=> 'registered user',
                    'usuario'=>$user
                ];
                
            }else{
                Yii::$app->getResponse()->setStatusCode(422,'Data Validation Failed.');
                $response = [
                    'success' => false,
                    'message' => 'Wrong parameters',
                    'usuario' => $user->errors,
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
        $user = Usuario::findOne($id);
        if ($user) {
            $url_image = $user-> url_image;
            $data = JSON::decode(Yii::$app->request->post('data'));
            $file = UploadedFile::getInstanceByName('file');

            
            $user->nombres = $data["nombres"];
            $user->username = $data["username"];
            if(isset($data["password"])){
                $user->password_hash = Yii::$app->getSecurity()->generatePasswordHash($data["password"]);
            }            
            $user->access_token = Yii::$app->security->generateRandomString();
            $user->tipo = $data["tipo"];
            if($url_image && $file){
                $pathFile = Yii::getAlias('@webroot/upload/'.$url_image);
                unlink($pathFile);
                $fileName = uniqid() . '.' . $file->getExtension();
                $user->url_image = $fileName;
                $file->saveAs(Yii::getAlias('@app/web/upload/') . $fileName);
            }else if($file){
                $fileName = uniqid() . '.' . $file->getExtension();
                $user->url_image = $fileName;
                $file->saveAs(Yii::getAlias('@app/web/upload/') . $fileName);
            }
            try {

                if ($user->save()) {

                    $response = [
                        'success' => true,
                        'message' => 'Usuario Actualizado',
                        'user' => $user
                    ];
                } else {
                    Yii::$app->getResponse()->setStatusCode(422, 'Data Validation Failed');
                    $response = [
                        'success' => false,
                        'message' => 'Existe errores en los campos',
                        'error' => $user->errors
                    ];
                }
            } catch (Exception $e) {
                Yii::$app->getResponse()->setStatusCode(500);
                $response = [
                    'success' => false,
                    'message' => 'Error de codigo',
                    'error' => $e->getMessage()
                ];
            }
        } else {
            Yii::$app->getResponse()->setStatusCode(404);
            $response = [
                'success' => false,
                'message' => 'Usuario no encontrado',
            ];
        }
        return $response;
    }
    public function actionIndex($pageSize = 5){
        $query = Usuario::find();

        $pagination = new Pagination([
            'defaultPageSize' => $pageSize,
            'totalCount' => $query->count(),
        ]);

        $users = $query
                        ->orderBy('id DESC')
                        ->offset($pagination->offset)
                        ->limit($pagination->limit)        
                        ->all();
        
        $currentPage = $pagination->getPage() + 1;
        $totalPages = $pagination->getPageCount();
        $response = [
        'success' => true,
        'message' => 'lista de clientes',
        'pageInfo' => [
            'next' => $currentPage == $totalPages ? null  : $currentPage + 1,
            'previus' => $currentPage == 1 ? null: $currentPage - 1,
            'count' => count($users),
            'page' => $currentPage,
            'start' => $pagination->getOffset(),
            'totalPages' => $totalPages,
            'users' => $users
            ]
        ];
        return $response;
    }

}
