<?php

namespace app\controllers;
use app\models\DetalleVenta;
use app\models\Venta;
use app\models\Periodo;
use Yii;
class VentaController extends \yii\web\Controller
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
    public function actionCreate( $userId, $customerId ){
        $params = Yii::$app->getRequest()-> getBodyParams();
        $numberOrder = Venta::find() -> all();
        $orderDetail = $params['orderDetail'];    
        $sale = new Venta();
        $sale -> fecha = date('Y-m-d h:i:s');
        $sale -> cantidad_total = intval($params['cantidadTotal']);
        $sale -> cantidad_cancelada = $params['cantidadPagada'];
        $sale -> usuario_id = $userId;
        $sale -> numero_pedido = count($numberOrder) + 1;
        $sale -> estado = $params['estado'];
        $sale -> tipo_pago = $params['tipoPago'];
        $sale -> cliente_id = $customerId;

        if($sale -> save()){
             //agregar detalle de venta
            foreach( $orderDetail as $order){
                $saleDetail = new DetalleVenta();
                $saleDetail -> cantidad = $order['cantidad'];
                $saleDetail -> producto_id = $order['id'];
                $saleDetail -> venta_id =  $sale -> id;
                if(!$saleDetail -> save()){
                    Yii::$app->getResponse()->setStatusCode(422, 'Data Validation Failed.');
                    $response = [
                        'success' => false,
                        'message' => 'Existen errores en los parametros',
                        'data' => $saleDetail->errors
                    ];
                }
            }

            Yii::$app->getResponse()->setStatusCode(201);
            $response = [
                'success' => true,
                'message' => 'failed update',
                'sale' => $sale
            ];
        }else{
            Yii::$app->getResponse()->setStatusCode(422, 'Data Validation Failed.');
            $response = [
                'success' => false,
                'message' => 'failed update',
                'data' => $sale->errors
            ];
        }
    return $response;
    }

    /* Retorna la lista de ventas del periodo */
    public function actionGetSales( $idPeriod, $idUser ){

        $period = Periodo::findOne( $idPeriod );

        $sales = Venta::find()
                        ->where(['fecha' >= $period-> fecha_inicio, 'usuario_id' => $idUser])
                        -> all();
        $response = [
            'success' => true, 
            'message' => 'Lista de ventas',
            'sales' => $sales
        ];
        
        return $response;
    }
}
