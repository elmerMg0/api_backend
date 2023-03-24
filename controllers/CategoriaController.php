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
                'update' => ['put', 'post'],
                'delete' => ['delete'],
                'get-category' => ['get'],

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
        $category = Categoria::findOne($idCategory);
        if ($category) {
            $params = Yii::$app->getRequest()->getBodyParams();
            $category->load($params, '');
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
    public function actionCategories(){
        $categories = Categoria::find()->all();
        $response = [
            'success' => true,
            'message' => 'Lista de categorias',
            'categories' => $categories
        ];
        return $response;
    }
}
