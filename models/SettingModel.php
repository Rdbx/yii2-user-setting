<?php

namespace Redbox\PersonalSettings\models;

use Redbox\PersonalSettings\behaviors\DatetimeBehavior;
use Redbox\PersonalSettings\models\enumerables\SettingStatus;
use Redbox\PersonalSettings\models\enumerables\SettingType;
use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%setting}}".
 *
 * @property int $id
 * @property int $user_id
 * @property string $type
 * @property string $section
 * @property string $key
 * @property string $value
 * @property bool $status
 * @property string $description
 * @property string $created_at
 * @property string $updated_at
 */
class SettingModel extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%personal_settings}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['section', 'key', 'value'], 'required'],
            [['user_id', 'section', 'key'], 'unique', 'targetAttribute' => ['user_id', 'section', 'key']],
            [['value', 'type'], 'string'],
            [['section', 'key', 'description'], 'string', 'max' => 255],
            [['user_id'], 'integer'],
            [['status'], 'integer'],
            ['status', 'default', 'value' => SettingStatus::ACTIVE],
            ['status', 'in', 'range' => SettingStatus::getConstantsByName()],
            [['type'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => Yii::t('yii2mod.settings', 'ID'),
            'user_id' => Yii::t('yii2mod.settings', 'User ID'),
            'type' => Yii::t('yii2mod.settings', 'Type'),
            'section' => Yii::t('yii2mod.settings', 'Section'),
            'key' => Yii::t('yii2mod.settings', 'Key'),
            'value' => Yii::t('yii2mod.settings', 'Value'),
            'status' => Yii::t('yii2mod.settings', 'Status'),
            'description' => Yii::t('yii2mod.settings', 'Description'),
            'created_at' => Yii::t('yii2mod.settings', 'Created Date'),
            'updated_at' => Yii::t('yii2mod.settings', 'Updated Date'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            DatetimeBehavior::class,
        ];
    }

    /**
     * Creates an [[ActiveQueryInterface]] instance for query purpose.
     */
    public static function find(): SettingQuery
    {
        return new SettingQuery(get_called_class());
    }

    /**
     * {@inheritdoc}
     */
    public function afterDelete()
    {
        parent::afterDelete();

        Yii::$app->personal_settings->invalidateCache();
    }

    /**
     * {@inheritdoc}
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        Yii::$app->personal_settings->invalidateCache();
    }

    /**
     * Return array of settings
     */
    public function getSettings($id): array
    {
        $result = [];
        $settings = static::find()
            ->select(['user_id', 'type', 'section', 'key', 'value'])
            ->andWhere(['user_id' => $id])
            ->active()
            ->asArray()
            ->all();

        foreach ($settings as $setting) {
            $section = $setting['section'];
            $key = $setting['key'];
            $settingOptions = [
                'type' => $setting['type'],
                'value' => $setting['value'],
            ];

            if (isset($result[$section][$key])) {
                ArrayHelper::merge($result[$section][$key], $settingOptions);
            } else {
                $result[$section][$key] = $settingOptions;
            }
        }

        return $result;
    }

    /**
     * Set setting
     *
     * @param $user_id
     * @param $section
     * @param $key
     * @param $value
     * @param null $type
     */
    public function setSetting($user_id, $section, $key, $value, $type = null): bool
    {
        $model = static::findOne([
            'user_id' => $user_id,
            'section' => $section,
            'key' => $key,
        ]);

        if (empty($model)) {
            $model = new static();
        }

        $model->user_id = $user_id;
        $model->section = $section;
        $model->key = $key;
        $model->value = strval($value);

        if ($type !== null && ArrayHelper::keyExists($type, SettingType::getConstantsByValue())) {
            $model->type = $type;
        } else {
            $model->type = gettype($value);
        }

        return $model->save();
    }

    /**
     * Remove setting
     *
     * @param $user_id
     * @param $section
     * @param $key
     *
     * @return bool|int|null
     *
     * @throws \Exception
     */
    public function removeSetting($user_id, $section, $key)
    {
        $model = static::findOne([
            'user_id' => $user_id,
            'section' => $section,
            'key' => $key,
        ]);

        if (!empty($model)) {
            return $model->delete();
        }

        return false;
    }

    /**
     * Remove all settings
     */
    public function removeAllSettings(): int
    {
        return static::deleteAll();
    }

    /**
     * Activates a setting
     *
     * @param $user_id
     * @param $section
     * @param $key
     *
     * @return bool
     */
    public function activateSetting($user_id, $section, $key): bool
    {
        $model = static::findOne(['user_id' => $user_id, 'section' => $section, 'key' => $key]);

        if ($model && $model->status === SettingStatus::INACTIVE) {
            $model->status = SettingStatus::ACTIVE;

            return $model->save(true, ['status']);
        }

        return false;
    }

    /**
     * Deactivates a setting
     *
     * @param $user_id
     * @param $section
     * @param $key
     *
     * @return bool
     */
    public function deactivateSetting($user_id, $section, $key): bool
    {
        $model = static::findOne(['user_id' => $user_id, 'section' => $section, 'key' => $key]);

        if ($model && $model->status === SettingStatus::ACTIVE) {
            $model->status = SettingStatus::INACTIVE;

            return $model->save(true, ['status']);
        }

        return false;
    }
}
