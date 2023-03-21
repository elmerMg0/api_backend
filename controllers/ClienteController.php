<?php

namespace app\controllers;
use Yii;
use \app\models\Cliente;
use Exception;
use yii\data\Pagination;
class ClienteController extends \yii\web\Controller
{
 

    public function behaviors(){
        $behaviors = parent::behaviors();
        $behaviors["verbs"] = [
            "class" => \yii\filters\VerbFilter::class,
            "actions" => [
                'index' => [ 'get'],
                'create' => [ 'post' ],
                'update' => [ 'put' ],
                'delete' => [ 'delete' ],
                'get-customer' => [ 'get' ],

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


    public function actionIndex( $pageSize = 5)
    {
        $query = Cliente::find();

        $pagination = new Pagination([
            'defaultPageSize' => $pageSize,
            'totalCount' => $query->count(),
        ]);

        $customers = $query
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
            'count' => count($customers),
            'page' => $currentPage,
            'start' => $pagination->getOffset(),
            'totalPages' => $totalPages,
            'customers' => $customers
            ]
        ];
        return $response;
    }

    public function actionCreate(){
        $params = Yii::$app->getRequest()->getBodyParams();
        $cliente = new Cliente();
        $cliente -> load($params,"");
        $cliente -> fecha_crecion = Date("H-m-d H:i:s");
        try{
            if($cliente->save()){
                //todo ok
                Yii::$app->getResponse()->setStatusCode(201);
                $response = [
                    "success" => true,
                    "message" => "Cliente agreado exitosamente",
                    'cliente' => $cliente
                ];
            }else{
                //Cuando hay error en los tipos de datos ingresados 
                Yii::$app->getResponse()->setStatusCode(422, 'Data Validation Failed');
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

    public function actionUpdate( $idCustomer ){
        $customer = Cliente::findOne($idCustomer);
        if($customer){
            $params = Yii::$app->getRequest()->getBodyParams();
            $customer -> load($params, '');
                try{

                    if($customer->save()){
                        $response = [
                            'success' => true,
                            'message' => 'Cliente actualizado correctamente',
                            'customer' => $customer
                        ];
                    }else{
                        Yii::$app->getResponse()->setStatusCode(422, 'Data Validation Failed');
                        $response = [
                            'success' => false,
                            'message' => 'Existe errores en los campos',
                            'error' => $customer->errors
                        ];
                    }
                }catch(Exception $e){
                    $response = [
                        'success' => false,
                        'message' => 'Cliente no encontrado',
                    ];
                }
        }else{
            $response = [
                'success' => false,
                'message' => 'Cliente no encontrado',
            ];
        }
        return $response;
    }

    public function actionGetCustomer($idCustomer){
        $customer = Cliente::findOne($idCustomer);
        if($customer){
            $response = [
                'success' => true,
                'message' => 'Accion realizada correctamente',
                'customer' => $customer
            ];
        }else{
            Yii::$app->getResponse()->setStatusCode(404);
            $response = [
                'success' => false,
                'message' => 'No existe el cliente',
                'customer' => $customer
            ];
        }
        return $response;
    }

    public function actionDelete( $idCustomer ){
        $customer = Cliente::findOne($idCustomer);

        if($customer){
            try{
                $customer->delete();
                $response = [
                    "success" => true,
                    "message" => "cliente eliminado correctamente",
                    "data" => $customer
                ];
            }catch(yii\db\IntegrityException $ie){
                Yii::$app->getResponse()->setStatusCode(409, "");
                $response = [
                    "success" => false,
                    "message" =>  "El cliente esta siendo usado",
                    "code" => $ie->getCode()
                ];
            }catch(\Exception $e){
                Yii::$app->getResponse()->setStatusCode(422, "");
                $response = [
                    "success" => false,
                    "message" => $e->getMessage(),
                    "code" => $e->getCode()
                ];
            }
        }else{
            Yii::$app->getResponse()->setStatusCode(404);
            $response = [
                "success" => false,
                "message" => "Cliente no encontrado"
            ];
        }
        return $response;
    }
}
