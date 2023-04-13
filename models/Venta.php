<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "venta".
 *
 * @property int $id
 * @property string $fecha
 * @property int|null $cantidad_total
 * @property int|null $cantidad_cancelada
 * @property int $usuario_id
 * @property int $numero_pedido
 * @property int $cliente_id
 * @property string $estado
 *
 * @property Cliente $cliente
 * @property DetalleVenta[] $detalleVentas
 * @property Usuario $usuario
 */
class Venta extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'venta';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['fecha', 'usuario_id', 'numero_pedido', 'cliente_id', 'estado'], 'required'],
            [['fecha'], 'safe'],
            [['cantidad_total', 'cantidad_cancelada', 'usuario_id', 'numero_pedido', 'cliente_id'], 'default', 'value' => null],
            [['cantidad_total', 'cantidad_cancelada', 'usuario_id', 'numero_pedido', 'cliente_id'], 'integer'],
            [['estado'], 'string', 'max' => 20],
            [['cliente_id'], 'exist', 'skipOnError' => true, 'targetClass' => Cliente::class, 'targetAttribute' => ['cliente_id' => 'id']],
            [['usuario_id'], 'exist', 'skipOnError' => true, 'targetClass' => Usuario::class, 'targetAttribute' => ['usuario_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'fecha' => 'Fecha',
            'cantidad_total' => 'Cantidad Total',
            'cantidad_cancelada' => 'Cantidad Cancelada',
            'usuario_id' => 'Usuario ID',
            'numero_pedido' => 'Numero Pedido',
            'cliente_id' => 'Cliente ID',
            'estado' => 'Estado',
        ];
    }

    /**
     * Gets query for [[Cliente]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCliente()
    {
        return $this->hasOne(Cliente::class, ['id' => 'cliente_id']);
    }

    /**
     * Gets query for [[DetalleVentas]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDetalleVentas()
    {
        return $this->hasMany(DetalleVenta::class, ['venta_id' => 'id']);
    }

    /**
     * Gets query for [[Usuario]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUsuario()
    {
        return $this->hasOne(Usuario::class, ['id' => 'usuario_id']);
    }
}
