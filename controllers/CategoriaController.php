<?php

namespace app\controllers;

use Yii;
use app\models\Categoria;
use app\models\UploadForm;
use Exception;
use yii\web\UploadedFile;
use yii\data\Pagination;

class CategoriaController extends \yii\web\Controller
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors["verbs"] = [
            "class" => \yii\filters\VerbFilter::class,
            "actions" => [
                'index' => ['get'],
                'create' => ['post'],
                'update' => ['put'],
                'delete' => ['delete'],
                'get-customer' => ['get'],

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

    public function actionIndex($pageSize = 5)
    {
        $query = Categoria::find();

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
            'message' => 'lista de categorias',
            'pageInfo' => [
                'next' => $currentPage == $totalPages ? null  : $currentPage + 1,
                'previus' => $currentPage == 1 ? null : $currentPage - 1,
                'count' => count($customers),
                'page' => $currentPage,
                'start' => $pagination->getOffset(),
                'totalPages' => $totalPages,
                'customers' => $customers
            ]
        ];
        return $response;
    }

    public function actionCreate()
    {
        $params = Yii::$app->getRequest()->getBodyParams();
        $category = new Categoria();
        $category->load($params, '');
        try {

            if ($category->save()) {
                $response = [
                    'success' => true,
                    'message' => 'Categoria creada exitosamente',
                    'category' => $category
                ];
            } else {
                $response = [
                    'success' => false,
                    'message' => 'Existen errores en los campos',
                    'category' => $category
                ];
            }
        } catch (Exception $e) {
            $response = [
                'success' => false,
                'message' => 'Ocurrio un error',
                'category' => $e->getMessage()
            ];
        }
        return $response;
    }

    public function actionUploadImage($idCategory)
    {
        $category = Categoria::findOne($idCategory);
        if ($category) {

            try {
                $file = UploadedFile::getInstanceByName('file');
                $fileName = uniqid() . '.' . $file->getExtension();
                $file->saveAs(Yii::getAlias('@app/web/upload/') . $fileName);
                $category->url_image = $fileName;
                $category->save();
                $response = [
                    'success' => true,
                    'message' => 'Imagen guardada exitosamente',
                    'fileName' => $category
                ];
            } catch (Exception $e) {
                //captura el error de cuando no exista el directorio donde se quiera almacenar la imagen 
                Yii::$app->getResponse()->setStatusCode(500);
                $response = [
                    'success' => false,
                    'message' => 'Ocurrio un error',
                    'error' => $e->getMessage()
                ];
            }
        } else {
            $response = [
                'success' => false,
                'message' => 'No existe categoria',
            ];
        }
        return $response;
    }

    public function actionUpdate($idCategory)
    {
        $customer = Categoria::findOne($idCategory);
        if ($customer) {
            $params = Yii::$app->getRequest()->getBodyParams();
            $customer->load($params, '');
            try {

                if ($customer->save()) {
                    $response = [
                        'success' => true,
                        'message' => 'Categoria actualizado correctamente',
                        'customer' => $customer
                    ];
                } else {
                    Yii::$app->getResponse()->setStatusCode(422, 'Data Validation Failed');
                    $response = [
                        'success' => false,
                        'message' => 'Existe errores en los campos',
                        'error' => $customer->errors
                    ];
                }
            } catch (Exception $e) {
                $response = [
                    'success' => false,
                    'message' => 'Categoria no encontrado',
                ];
            }
        } else {
            $response = [
                'success' => false,
                'message' => 'Categoria no encontrado',
            ];
        }
        return $response;
    }

    public function actionGetCategory($idCategory)
    {
        $customer = Categoria::findOne($idCategory);
        if ($customer) {
            $response = [
                'success' => true,
                'message' => 'Accion realizada correctamente',
                'customer' => $customer
            ];
        } else {
            Yii::$app->getResponse()->setStatusCode(404);
            $response = [
                'success' => false,
                'message' => 'No existe el Categoria',
                'customer' => $customer
            ];
        }
        return $response;
    }

    public function actionDelete( $idCategory ){
        $category = Categoria::findOne($idCategory);

        if($category){
            try{
                $category->delete();
                $response = [
                    "success" => true,
                    "message" => "Categoria eliminado correctamente",
                    "category" => $category
                ];
            }catch(yii\db\IntegrityException $ie){
                Yii::$app->getResponse()->setStatusCode(409, "");
                $response = [
                    "success" => false,
                    "message" =>  "El Categoria esta siendo usado",
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
                "message" => "Categoria no encontrado"
            ];
        }
        return $response;
    }
}
