<?php

namespace Redbox\PersonalSettings\components;

use Yii;
use yii\base\Component;
use yii\caching\Cache;
use yii\di\Instance;
use yii\helpers\ArrayHelper;
use Redbox\PersonalSettings\models\enumerables\SettingType;

/**
 * Class PersonalSettings
 *
 * @package Redbox\PersonalSettings\components
 */
class PersonalSettings extends Component
{
    /**
     * @var string setting model class name
     */
    public $modelClass = \Redbox\PersonalSettings\models\SettingModel::class;

    /**
     * @var Cache|array|string the cache used to improve RBAC performance. This can be one of the followings:
     *
     * - an application component ID (e.g. `cache`)
     * - a configuration array
     * - a [[yii\caching\Cache]] object
     *
     * When this is not set, it means caching is not enabled
     */
    public $cache = 'cache';

    /**
     * @var string the key used to store settings data in cache
     */
    public $cacheKey = 'redbox-personal-setting';

    /**
     * @var \Redbox\PersonalSettings\models\SettingModel setting model
     */
    protected $model;

    /**
     * @var array list of settings
     */
    protected $items;

    /**
     * @var mixed setting value
     */
    protected $setting;

    /**
     * Initialize the component
     */
    public function init()
    {
        parent::init();

        if ($this->cache !== null) {
            $this->cache = Instance::ensure($this->cache, Cache::class);
        }

        $this->model = Yii::createObject($this->modelClass);
    }

    /**
     * Get's all values in the specific section.
     *
     * @param string $section
     * @param null $default
     *
     * @return mixed
     */
    public function getAllBySection($section, $default = null)
    {
        $items = $this->getSettingsConfig();

        if (isset($items[$section])) {
            $this->setting = ArrayHelper::getColumn($items[$section], 'value');
        } else {
            $this->setting = $default;
        }

        return $this->setting;
    }

    /**
     * Get's the value for the given section and key.
     *
     * @param string $section
     * @param string $key
     * @param null $default
     *
     * @return mixed
     */
    public function get($id, $section, $key, $default = null)
    {
        $items = $this->getSettingsConfig($id);

        if (isset($items[$section][$key])) {
            $this->setting = ArrayHelper::getValue($items[$section][$key], 'value');
            $type = ArrayHelper::getValue($items[$section][$key], 'type');
            $this->convertSettingType($type);
        } else {
            $this->setting = $default;
        }

        return $this->setting;
    }

    /**
     * Add a new setting or update an existing one.
     *
     * @param null $section
     * @param string $key
     * @param string $value
     * @param null $type
     *
     * @return bool
     */
    public function set($id, $section, $key, $value, $type = null): bool
    {
        if ($this->model->setSetting($id, $section, $key, $value, $type)) {
            if ($this->invalidatePersonalCache($id)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checking existence of setting
     *
     * @param string $section
     * @param string $key
     *
     * @return bool
     */
    public function has($id, $section, $key): bool
    {
        $setting = $this->get($id, $section, $key);

        return !empty($setting);
    }

    /**
     * Remove setting by section and key
     *
     * @param string $section
     * @param string $key
     *
     * @return bool
     */
    public function remove($id, $section, $key): bool
    {
        if ($this->model->removeSetting($id, $section, $key)) {
            if ($this->invalidatePersonalCache($id)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Remove all settings
     *
     * @return int
     */
    public function removeAll(): int
    {
        return $this->model->removeAllSettings();
    }

    /**
     * Activates a setting
     *
     * @param string $key
     * @param string $section
     *
     * @return bool
     */
    public function activate($id, $section, $key): bool
    {
        return $this->model->activateSetting($id, $section, $key);
    }

    /**
     * Deactivates a setting
     *
     * @param string $key
     * @param string $section
     *
     * @return bool
     */
    public function deactivate($id, $section, $key): bool
    {
        return $this->model->deactivateSetting($id, $section, $key);
    }

    /**
     * Returns the settings config
     *
     * @return array
     */
    protected function getSettingsConfig($id): array
    {
        if (!$this->cache instanceof Cache) {
            $this->items = $this->model->getSettings($id);
        } else {
            $cacheItems = $this->cache->get("{$this->cacheKey}-{$id}");
            if (!empty($cacheItems)) {
                $this->items = $cacheItems;
            } else {
                $this->items = $this->model->getSettings($id);
                $this->cache->set("{$this->cacheKey}-{$id}", $this->items);
            }
        }

        return $this->items;
    }

    /**
     * Invalidate the cache
     *
     * @return bool
     */
    public function invalidatePersonalCache($id): bool
    {
        if ($this->cache !== null) {
            $this->cache->delete("{$this->cacheKey}-{$id}");
            $this->items = null;
        }

        return true;
    }

    /**
     * Set type for setting
     *
     * @param $type
     */
    protected function convertSettingType($type)
    {
        if ($type === SettingType::BOOLEAN_TYPE) {
            $this->setting = filter_var($this->setting, FILTER_VALIDATE_BOOLEAN);
        } else {
            settype($this->setting, $type);
        }
    }
}
