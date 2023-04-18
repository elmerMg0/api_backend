<?php

namespace app\controllers;

use Yii;
use app\models\Periodo;
use app\models\Usuario;
use app\models\Venta;

class PeriodoController extends \yii\web\Controller
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['verbs'] = [
            'class' => \yii\filters\VerbFilter::class,
            'actions' => [
                'login' => ['POST'],
                'create' => ['POST'],
                'update' => ['POST'],
                'start-period' => ['POST'],
                'close-period' => ['Get']

            ]
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

    public function actionStartPeriod($userId)
    {
        $params = Yii::$app->getRequest()->getBodyParams();
        //validar que no exista un periodo 
        //$lastRecord = Periodo::find()->orderBy(['id' => SORT_DESC])->one();
        //if ($lastRecord) {
          //  if (!$lastRecord->estado) {
                $period = new Periodo();
                $period->fecha_inicio = Date('H-m-d H:i:s');
                $period->estado = true;
                $period->caja_inicial = $params['cajaInicial'];
                $period->usuario_id = $userId;
                if ($period->save()) {
                    $response = [
                        'success' => true,
                        'message' => 'Periodo iniciado con exito!',
                        'period' => $period
                    ];
                } else {
                    $response = [
                        'success' => false,
                        'message' => 'Existen parametros incorrectos',
                        'errors' => $period->errors
                    ];
                }
          /*   } else {
                $response = [
                    'success' => false,
                    'message' => 'Existe ya un periodo activo',
                    'periodActive' => $lastRecord
                ];
            } */
      /*   } else {
            $response = [
                'success' => false,
                'message' => 'No existen periodos aun ',
            ];
        } */

        return $response;
    }
    public function actionClosePeriod( $idPeriod )
    {
        $params = Yii::$app->getRequest()->getBodyParams();
        $period = Periodo::findOne($idPeriod);
        $period->fecha_fin = Date('H-m-d H:i:s');
        $period->estado = false;
        $period->total_ventas = 'calcular total ventas por periodo';
        $period->total_cierre_caja = $params['totalCierreCaja'];
        if ($period->save()) {
            $response = [
                'success' => true,
                'message' => 'Periodo iniciado con exito!',
                'period' => $period
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'Existen parametros incorrectos',
                'errors' => $period->errors
            ];
        }
        return $response;
    }

    public function getDetailPeriod($idUser, $idPeriod)
    {
        $period = Periodo::findOne($idPeriod);
        if ($period) {
            $user = Usuario::findOne($idUser);
            if ($user) {
                //vetnas totales hasta el momento 
                $totalSaleCash = Venta::find()
                    ->where(['fecha' >= $period->fecha_inicio, 'usuario_id' => $user->id, 'tipo_pag' => 'efectivo'])
                    ->sum('cantidad_total');

                $totalSaleCard = Venta::find()
                    ->where(['fecha' >= $period->fecha_inicio, 'usuario_id' => $user->id, 'tipo_pag' => 'tarjeta'])
                    ->sum('cantidad_total');

                $totalSaleTransfer = Venta::find()
                    ->where(['fecha' >= $period->fecha_inicio, 'usuario_id' => $user->id, 'tipo_pag' => 'transferencia'])
                    ->sum('cantidad_total');

                $response = [
                    'success' => true,
                    'message' => 'detalle de periodo por usuario',
                    'info' => [
                        'user' => $user,
                        'period' => $period,
                        'totalSaleCash' => $totalSaleCash,
                        'totalSaleCard' => $totalSaleCard,
                        'totalSaleTransfer' => $totalSaleTransfer
                        ]
                    ];
            } else {
                $response = [
                    'success' => false,
                    'message' => 'No existe usuario',
                    'user' => $idUser
                ];
            }
        } else {
            $response = [
                'success' => false,
                'message' => 'No existe periodo',
                'period' => $idPeriod
            ];
        }
        return $response;
    }

    public function actionExistsPeriod(){
        $lastRecord = Periodo::find()->orderBy(['id' => SORT_DESC])->one();
        if($lastRecord){
            if( $lastRecord -> estado) {
                $response = [
                    'success' => true, 
                    'message' => 'existe periodo activo',
                    'period' => true
                ];
            }else{
                $response = [
                    'success' => false, 
                    'message' => 'existe periodoa activo',
                    'period' => false    
                ];
            }
        }else{
            $response = [
                'success' => true, 
                'message' => 'No existen periodos aun',
                'period' => false    
            ];
        }
        return $response;
    }
}
