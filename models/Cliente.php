<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "cliente".
 *
 * @property int $id
 * @property string $nombre
 * @property int|null $celular
 * @property string|null $direccion
 * @property string|null $descripcion_domicilio
 * @property string $fecha_crecion
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
            [['nombre', 'fecha_crecion'], 'required'],
            [['celular'], 'default', 'value' => null],
            [['celular'], 'integer'],
            [['fecha_crecion'], 'safe'],
            [['nombre', 'direccion'], 'string', 'max' => 80],
            [['descripcion_domicilio'], 'string', 'max' => 100],
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
            'celular' => 'Celular',
            'direccion' => 'Direccion',
            'descripcion_domicilio' => 'Descripcion Domicilio',
            'fecha_crecion' => 'Fecha Crecion',
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
