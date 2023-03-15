<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "cliente".
 *
 * @property int $id
 * @property string $nombre
 * @property int|null $telefono
 * @property int $celular
 * @property string|null $direccion
 * @property string|null $descripcion
 * @property string $fecha_creacion
 *
 * @property Venta[] $ventas
 */
class Cliente extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'cliente';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['nombre', 'celular', 'fecha_creacion'], 'required'],
            [['telefono', 'celular'], 'default', 'value' => null],
            [['telefono', 'celular'], 'integer'],
            /* [['fecha_creacion'], 'safe'], */
            [['nombre'], 'string', 'max' => 50],
            [['direccion'], 'string', 'max' => 80],
            [['descripcion'], 'string', 'max' => 100],
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
            'telefono' => 'Telefono',
            'celular' => 'Celular',
            'direccion' => 'Direccion',
            'descripcion' => 'Descripcion',
            'fecha_creacion' => 'Fecha Creacion',
        ];
    }

    /**
     * Gets query for [[Ventas]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVentas()
    {
        return $this->hasMany(Venta::class, ['cliente_id' => 'id']);
    }
}
