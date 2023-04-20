<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "empresa".
 *
 * @property int $id
 * @property string $nombre
 * @property string $email
 * @property int|null $phone
 * @property int|null $celular
 * @property int|null $nit
 */
class Empresa extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'empresa';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['nombre', 'email'], 'required'],
            [['phone', 'celular', 'nit'], 'default', 'value' => null],
            [['phone', 'celular', 'nit'], 'integer'],
            [['nombre'], 'string', 'max' => 50],
            [['email'], 'string', 'max' => 80],
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
            'email' => 'Email',
            'phone' => 'Phone',
            'celular' => 'Celular',
            'nit' => 'Nit',
        ];
    }
}
