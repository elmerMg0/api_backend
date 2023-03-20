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
 *
 * @property Periodo[] $periodos
 * @property Venta[] $ventas
 */
class Usuario extends \yii\db\ActiveRecord implements \yii\web\IdentityInterface
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
            [['username', 'nombres'], 'required'],
            [['password_hash', 'access_token'], 'string'],
            [['username'], 'string', 'max' => 50],
            [['nombres'], 'string', 'max' => 80],
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
        ];
    }
/**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        //return isset(self::$users[$id]) ? new static(self::$users[$id]) : null;
    }
    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        $user = Usuario::findOne(['access_token' => $token]);     	
        if ($user) {      
        // Evita mostrar el token de usuario   	
        $user->access_token = null; 
        // Almacena el usuario en Yii::$app->user->identity  
        return new static($user);     	
        }     	
        return null; // Almacena null en Yii::$app->user->identity
        
    }
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
        return $this->authKey;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
        return $this->authKey === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return $this->password === $password;
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
