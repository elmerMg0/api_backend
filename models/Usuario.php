<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "usuario".
 *
 * @property int $id
 * @property string $username
 * @property string|null $password_hash
 * @property string|null $access_token
 * @property string $nombres
 * @property string|null $url_image
 * @property string $tipo
 *
 * @property Periodo[] $periodos
 * @property Venta[] $ventas
 */
class Usuario extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'usuario';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['username', 'nombres', 'tipo'], 'required'],
            [['password_hash', 'access_token'], 'string'],
            [['username', 'tipo'], 'string', 'max' => 50],
            [['nombres'], 'string', 'max' => 80],
            [['url_image'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Username',
            'password_hash' => 'Password Hash',
            'access_token' => 'Access Token',
            'nombres' => 'Nombres',
            'url_image' => 'Url Image',
            'tipo' => 'Tipo',
        ];
    }

    /**
     * Gets query for [[Periodos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPeriodos()
    {
        return $this->hasMany(Periodo::class, ['usuario_id' => 'id']);
    }

    /**
     * Gets query for [[Ventas]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVentas()
    {
        return $this->hasMany(Venta::class, ['usuario_id' => 'id']);
    }
}
