<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "sub_producto".
 *
 * @property int $id
 * @property string $nombre
 * @property int $producto_id
 *
 * @property Producto $producto
 */
class SubProducto extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'sub_producto';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['nombre', 'producto_id'], 'required'],
            [['producto_id'], 'default', 'value' => null],
            [['producto_id'], 'integer'],
            [['nombre'], 'string', 'max' => 50],
            [['producto_id'], 'exist', 'skipOnError' => true, 'targetClass' => Producto::class, 'targetAttribute' => ['producto_id' => 'id']],
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
            'producto_id' => 'Producto ID',
        ];
    }

    /**
     * Gets query for [[Producto]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProducto()
    {
        return $this->hasOne(Producto::class, ['id' => 'producto_id']);
    }
}
