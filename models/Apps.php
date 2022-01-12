<?php

namespace app\models;

use app\basic\debugHelper;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\FileHelper;
use yii\web\UploadedFile;

/**
 * This is the model class for table "{{%tbl_apps}}".
 *
 * @property int $id
 * @property string $name
 * @property string $package
 * @property int $published
 * @property string|null $extra
 */
class Apps extends ActiveRecord
{
    public $file;
    public $keystore_file;

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%tbl_apps}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'package'], 'required'],
            ['package', 'unique'],
            [['created_time', 'upload_time', 'published_time', 'banned_time', 'ready_time', 'testing_time', 'revision_time', 'lastchecked_time'], 'safe'],
            [['published', 'traffic_route', 'created_code_user_id', 'builder_code_user_id'], 'integer'],
            ['published', 'in', 'range' => [-1, 0, 1, 2, 3, 4, 5]], // -1 = ban, 0 = no pub, 1 = pub, 2 = process, 3 = test, 4 = ready, 5 = Revision
            [['market_detailed', 'note', 'fb_app_id', 'app_secret', 'uuid'], 'string'],
            [['github'], 'string'],
            [['name', 'package'], 'string', 'max' => 255],
            [['file'], 'file', 'extensions' => 'apk', 'maxSize' => 1024*1024*70], //70MB
            [['keystore_file'], 'file', 'extensions' => 'keystore', 'maxSize' => 1024*10], //10кб
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'name'),
            'package' => Yii::t('app', 'package'),
            'published' => Yii::t('app', 'status'),
            'note' => Yii::t('app', 'note'),
            'created_code_user_id' => Yii::t('app', 'created_code_user_id'),
            'builder_code_user_id' => Yii::t('app', 'builder_code_user_id'),
            'file' => 'APK файл',
            'keystore_file' => 'Keystore',
            'github' => 'Github',
            'fb_app_id' => 'Fb_app_id',
            'app_secret' => 'App_secret',
        ];
    }


    public function getPrices(){
        return $this->hasMany(Prices::class, ['app_id' => 'id']);
    }


    public function getLinkcountries(){
		return $this->hasMany(Linkcountries::class, ['app_id' => 'id']);
	}

    public function getAppaccess(){
        return $this->hasMany(AppsAccess::class, ['app_id' => 'id']);
    }

    public function getUsers(){
        //return $this->hasMany(Linkcountries::className(), ['app_id' => 'id']);
        return $this->hasMany(
            Linkcountries::class,
            ['app_id'=>'id'],
            function($query){
                $query->where(['user_id'=>18]);
            }
        );
    }

    public function beforeSave($insert)
    {
        $this->saveFile('file', 'apk', 'apk');
        $this->saveFile('keystore_file', 'keystore', 'keystore');

        return parent::beforeSave($insert);
    }

    private function saveFile($field, $attribute, $alias)
    {
        if($file = UploadedFile::getInstance($this, $field)) {
            $dir = Yii::getAlias('@'.$alias) . '/';
            if(!file_exists($dir)) {
                FileHelper::createDirectory($dir);
            }

            if (!is_dir($dir . $this->$attribute)) {
                if (file_exists($dir . $this->$attribute)) {
                    unlink($dir . $this->$attribute);
                }
            }

            $this->$attribute = $file->baseName . '_' . Yii::$app->getSecurity()->generateRandomString(6) . '.' . $file->extension;
            if(!$file->saveAs($dir.$this->$attribute)) {
                debugHelper::print($file->error);
            }
        }
    }

    public static function getAccessApp($user_id, $app_id)
    {
        $connection = Yii::$app->getDb();
        $accessApp = $connection->createCommand("
                SELECT
                tbl_links.user_id,
                tbl_linkcountries.app_id
                FROM
                tbl_links
                INNER JOIN tbl_linkcountries ON tbl_linkcountries.id = tbl_links.linkcountry_id
                WHERE tbl_links.user_id = :user_id
                AND tbl_links.archived = 0
                AND tbl_linkcountries.app_id = :app_id
            ", [':user_id' => $user_id, ':app_id' => $app_id]);
        $accessApp = $accessApp->queryAll();
        return $accessApp ?? false;
    }

    public static function getApp($package)
    {
        $app = self::find()->where(['package' => $package])->one();
        if ($app) {
            return $app;
        } else {
            return false;
        }
    }

}
