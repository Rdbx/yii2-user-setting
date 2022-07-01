<?php

namespace Redbox\PersonalSettings\models\enumerables;

use yii2mod\enum\helpers\BaseEnum;

/**
 * Class SettingStatus
 *
 * @package Redbox\PersonalSettings\models\enumerables
 */
class SettingStatus extends BaseEnum
{
    const ACTIVE = 1;
    const INACTIVE = 0;

    /**
     * @var string message category
     */
    public static $messageCategory = 'yii2mod.settings';

    /**
     * @var array
     */
    public static $list = [
        self::ACTIVE => 'Active',
        self::INACTIVE => 'Inactive',
    ];
}
