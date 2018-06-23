<?php

namespace lav45\behaviors\traits;

use yii\helpers\ArrayHelper;

/**
 * Trait WatchAttributesTrait
 * @package lav45\behaviors\traits
 */
trait WatchAttributesTrait
{
    /**
     * @var array
     * [
     *     [
     *         - watch: string|array|true => watch for changes in a few fields,
     *         - field: string => set value in this relation attribute,
     *         - value: string|array|\Closure => get value from the attribute or path,
     *     ],
     * ]
     */
    private $attributes = [];

    /**
     * The method converts the value of the attributes field to a common form
     * @param array $attributes
     * Example: [
     *      'id',
     *      'login' => 'user_login',
     *      'login' => [
     *          'field' => 'user_login',
     *          // 'watch' => 'login',
     *          // 'value' => 'login',
     *      ],
     *      [
     *          'watch' => 'status',
     *          // 'watch' => true, // always send this data
     *          // 'watch' => ['status', 'username'],
     *
     *          'field' => 'statusName',
     *
     *          'value' => 'status',
     *          // 'value' => 'array.key',
     *          // 'value' => ['array', 'key'],
     *          // 'value' => function($owner) {
     *          //     return $owner->array['key'];
     *          // },
     *      ],
     * ]
     */
    public function setAttributes(array $attributes)
    {
        $this->attributes = [];
        foreach ($attributes as $key => $val) {
            if (is_int($key) && is_string($val)) {
                $key = $val;
            }
            if (empty($val['watch'])) {
                $watch = $key;
            } else {
                $watch = $val['watch'];
            }
            if (is_string($val)) {
                $field = $val;
            } elseif (empty($val['field'])) {
                $field = $key;
            } else {
                $field = $val['field'];
            }
            if (empty($val['value'])) {
                $value = $key;
            } else {
                $value = $val['value'];
            }
            $this->attributes[] = [
                'watch' => $watch,
                'field' => $field,
                'value' => $value,
            ];
        }
    }

    /**
     * @param object $model
     * @param array $attributes
     */
    private function updateModel($model, $attributes)
    {
        foreach ($attributes as $attribute) {
            $model->{$attribute['field']} = ArrayHelper::getValue($this->owner, $attribute['value']);
        }
    }

    /**
     * @param array $changedAttributes
     * @return array
     */
    private function getChangedAttributes($changedAttributes)
    {
        $result = [];
        foreach ($this->attributes as $attribute) {
            $watch = $attribute['watch'];
            if (true === $watch) {
                $result[] = $attribute;
                continue;
            }
            if (is_array($watch)) {
                foreach ($watch as $item) {
                    if (isset($changedAttributes[$item]) || array_key_exists($item, $changedAttributes)) {
                        $result[] = $attribute;
                        break;
                    }
                }
                continue;
            }
            if (isset($changedAttributes[$watch]) || array_key_exists($watch, $changedAttributes)) {
                $result[] = $attribute;
            }
        }
        return $result;
    }
}