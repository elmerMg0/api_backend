<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "producto".
 *
 * @property int $id
 * @property string $nombre
 * @property string $tipo
 * @property float $precio_venta
 * @property float $precio_compra
 * @property string|null $descripcion
 * @property int|null $stock
 * @property int $categoria_id
 * @property string|null $url_image
 *
 * @property Categoria $categoria
 * @property DetalleVenta[] $detalleVentas
 * @property SubProducto[] $subProductos
 */
class Producto extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'producto';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['nombre', 'tipo', 'precio_venta', 'precio_compra', 'categoria_id'], 'required'],
            [['precio_venta', 'precio_compra'], 'number'],
            [['stock', 'categoria_id'], 'default', 'value' => null],
            [['stock', 'categoria_id'], 'integer'],
            [['url_image'], 'string'],
            [['nombre', 'tipo'], 'string', 'max' => 50],
            [['descripcion'], 'string', 'max' => 80],
            [['categoria_id'], 'exist', 'skipOnError' => true, 'targetClass' => Categoria::class, 'targetAttribute' => ['categoria_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'nombre' => 'Nombre',
            'tipo' => 'Tipo',
            'precio_venta' => 'Precio Venta',
            'precio_compra' => 'Precio Compra',
            'descripcion' => 'Descripcion',
            'stock' => 'Stock',
            'categoria_id' => 'Categoria ID',
            'url_image' => 'Url Image',
        ];
    }

    /**
     * Gets query for [[Categoria]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCategoria()
    {
        return $this->hasOne(Categoria::class, ['id' => 'categoria_id']);
    }

    /**
     * Gets query for [[DetalleVentas]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDetalleVentas()
    {
        return $this->hasMany(DetalleVenta::class, ['producto_id' => 'id']);
    }

    /**
     * Gets query for [[SubProductos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSubProductos()
    {
        return $this->hasMany(SubProducto::class, ['producto_id' => 'id']);
    }
}
