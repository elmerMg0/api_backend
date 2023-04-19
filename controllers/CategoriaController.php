<?php

namespace app\controllers;

use Yii;
use app\models\Categoria;
use app\models\UploadForm;
use Exception;
use yii\web\UploadedFile;
use yii\data\Pagination;
use yii\helpers\Json;

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
                'update' => ['put', 'post'],
                'delete' => ['delete'],
                'get-category' => ['get'],

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

    public function actionIndex($pageSize = 5)
    {
        $query = Categoria::find();

        $pagination = new Pagination([
            'defaultPageSize' => $pageSize,
            'totalCount' => $query->count(),
        ]);

        $categories = $query
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
                'count' => count($categories),
                'page' => $currentPage,
                'start' => $pagination->getOffset(),
                'totalPages' => $totalPages,
                'categories' => $categories
            ]
        ];
        return $response;
    }

    public function actionCreate()
    {
   
        $category = new Categoria;
        $file = UploadedFile::getInstanceByName('file');
        $data = Json::decode(Yii::$app->request->post('data'));

        // $data = Json::decode(Yii::$app->request->post('data'));
        if($file){
            $fileName = uniqid() . '.' . $file->getExtension();
            $file->saveAs(Yii::getAlias('@app/web/upload/') . $fileName);
            $category->url_image = $fileName;
        }
        try {
        $category->nombre = $data['nombre'];
        $category->descripcion = $data['descripcion'];


            if ($category->save()) {
                Yii::$app->getResponse()->setStatusCode(201);
                $response = [
                    'success' => true,
                    'message' => 'Categoria creada exitosamente',
                    'fileName' => $category
                ];
            } else {
                Yii::$app->getResponse()->setStatusCode(422,"Data Validation Failed.");
                $response = [
                    'success' => false,
                    'message' => 'Existen errores en los campos',
                    'errors' => $category->errors
                ];
            }
        } catch (Exception $e) {
            Yii::$app->getResponse()->setStatusCode(500);
            $response = [
                'success' => false,
                'message' => 'ocurrio un error',
                'fileName' => $e->getMessage()
            ];
        }

        return $response;
    }


    public function actionUpdate($idCategory)
    {
        $category = Categoria::findOne($idCategory);
        if ($category) {
            $url_image = $category-> url_image;
            $data = JSON::decode(Yii::$app->request->post('data'));
            $file = UploadedFile::getInstanceByName('file');

            
            $category->nombre = $data['nombre'];
            $category->descripcion = $data['descripcion'];
            if($url_image && $file){
                $pathFile = Yii::getAlias('@webroot/upload/'.$url_image);
                unlink($pathFile);
                $fileName = uniqid() . '.' . $file->getExtension();
                $category->url_image = $fileName;
                $file->saveAs(Yii::getAlias('@app/web/upload/') . $fileName);
            }else if($file){
                $fileName = uniqid() . '.' . $file->getExtension();
                $category->url_image = $fileName;
                $file->saveAs(Yii::getAlias('@app/web/upload/') . $fileName);
            }
            try {

                if ($category->save()) {

                    $response = [
                        'success' => true,
                        'message' => 'Categoria actualizado correctamente',
                        'category' => $category
                    ];
                } else {
                    Yii::$app->getResponse()->setStatusCode(422, 'Data Validation Failed');
                    $response = [
                        'success' => false,
                        'message' => 'Existe errores en los campos',
                        'error' => $category->errors
                    ];
                }
            } catch (Exception $e) {
                Yii::$app->getResponse()->setStatusCode(500);
                $response = [
                    'success' => false,
                    'message' => 'Categoria no encontrado',
                    'error' => $e->getMessage()
                ];
            }
        } else {
            Yii::$app->getResponse()->setStatusCode(404);
            $response = [
                'success' => false,
                'message' => 'Categoria no encontrado',
            ];
        }
        return $response;
    }

    public function actionGetCategory($idCategory)
    {
        $category = Categoria::findOne($idCategory);
        if ($category) {
            $response = [
                'success' => true,
                'message' => 'Accion realizada correctamente',
                'category' => $category
            ];
        } else {
            Yii::$app->getResponse()->setStatusCode(404);
            $response = [
                'success' => false,
                'message' => 'No existe el Categoria',
                'category' => $category
            ];
        }
        return $response;
    }

    public function actionDelete($idCategory)
    {
        $category = Categoria::findOne($idCategory);

        if ($category) {
            try {
                $url_image = $category->url_image;
                $category->delete();
                $pathFile = Yii::getAlias('@webroot/upload/'.$url_image);
                unlink($pathFile);
                $response = [
                    "success" => true,
                    "message" => "Categoria eliminado correctamente",
                    "category" => $category
                ];
            } catch (yii\db\IntegrityException $ie) {
                Yii::$app->getResponse()->setStatusCode(409, "");
                $response = [
                    "success" => false,
                    "message" =>  "El Categoria esta siendo usado",
                    "code" => $ie->getCode()
                ];
            } catch (\Exception $e) {
                Yii::$app->getResponse()->setStatusCode(422, "");
                $response = [
                    "success" => false,
                    "message" => $e->getMessage(),
                    "code" => $e->getCode()
                ];
            }
        } else {
            Yii::$app->getResponse()->setStatusCode(404);
            $response = [
                "success" => false,
                "message" => "Categoria no encontrado"
            ];
        }
        return $response;
    }
    public function actionCategories()
    {
        $categories = Categoria::find()->all();
        $response = [
            'success' => true,
            'message' => 'Lista de categorias',
            'categories' => $categories
        ];
        return $response;
    }

    public function actionGetProductsByCategory($idCategory){
        $category = Categoria::findOne($idCategory);
        if($category){
            $products = $category->getProductos()->all();
            $response = [
                "success" => true,
                "message" => "Lista de productos por categoria",
                "category" => $category,
                "products" => $products
            ];
        }else{
            Yii::$app->getResponse()->setStatusCode(404);
            $response = [
                "success" => false,
                "message" => "Categoria no encontrada",
            ];
        }
        return $response;
    }
}
