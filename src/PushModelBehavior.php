<?php
/**
 * Created by PhpStorm.
 * User: and1
 * Date: 14.05.2018
 * Time: 21:18
 */

namespace common\components\behaviors;

use Yii;
use yii\base\Model;
use yii\base\Behavior;
use yii\db\ActiveRecord;
use yii\db\AfterSaveEvent;
use yii\helpers\ArrayHelper;
use yii\base\InvalidConfigException;

/**
 * Class PushModelBehavior
 */
class PushModelBehavior extends Behavior
{
    /**
     * @var string
     */
    public $targetClass;
    /**
     * @var string|\Closure|bool
     */
    public $triggerAfterInsert = 'save';
    /**
     * @var string|\Closure|bool
     */
    public $triggerAfterUpdate = 'save';
    /**
     * @var string|\Closure|bool
     */
    public $triggerBeforeDelete = 'delete';
    /**
     * @var array
     * [
     *     [
     *         - watch: string|array => watch for changes in a few fields,
     *         - field: string => set value in this relation attribute,
     *         - value: string|array => get value from the attribute or path,
     *     ],
     * ]
     */
    private $attributes = [];

    /**
     * @inheritdoc
     */
    public function events()
    {
        $events = [];
        if ($this->triggerAfterInsert !== false) {
            $events[ActiveRecord::EVENT_AFTER_INSERT] = 'afterInsert';
        }
        if ($this->triggerAfterUpdate !== false) {
            $events[ActiveRecord::EVENT_AFTER_UPDATE] = 'afterUpdate';
        }
        if ($this->triggerBeforeDelete !== false) {
            $events[ActiveRecord::EVENT_AFTER_DELETE] = 'beforeDelete';
        }
        return $events;
    }

    /**
     * @throws InvalidConfigException
     */
    public function afterInsert()
    {
        $model = $this->getTargetModel();
        $this->updateItem($model, $this->attributes);
        if ($this->trigger($model, $this->triggerAfterInsert) === false) {
            Yii::warning('PushModelBehavior: Create failed!');
        }
    }

    /**
     * @param AfterSaveEvent $event
     * @throws InvalidConfigException
     */
    public function afterUpdate(AfterSaveEvent $event)
    {
        if ($changedAttributes = $this->getChangedAttributes($event->changedAttributes)) {
            $model = $this->getTargetModel();
            $this->updateItem($model, $changedAttributes);
            if ($this->trigger($model, $this->triggerAfterUpdate) === false) {
                Yii::warning('PushModelBehavior: Update failed!');
            }
        }
    }

    /**
     * @throws InvalidConfigException
     */
    public function beforeDelete()
    {
        $model = $this->getTargetModel();
        $this->updateItem($model, $this->attributes);
        if ($this->trigger($model, $this->triggerBeforeDelete) === false) {
            Yii::warning('PushModelBehavior: Delete failed!');
        }
    }

    /**
     * @param Model $model
     * @param $triggerFunc
     * @return mixed
     */
    private function trigger($model, $triggerFunc)
    {
        if (is_callable($triggerFunc)) {
            return call_user_func($triggerFunc, $model);
        } else {
            return call_user_func([$model, $triggerFunc]);
        }
    }

    /**
     * @return Model
     * @throws InvalidConfigException
     */
    protected function getTargetModel()
    {
        if ($this->targetClass === null) {
            throw new InvalidConfigException('PushModelBehavior: Wrong class!');
        }

        return new $this->targetClass;
    }

    /**
     * @param Model $model
     * @param array $attributes
     */
    protected function updateItem($model, $attributes)
    {
        foreach ($attributes as $attribute) {
            $model->{$attribute['field']} = ArrayHelper::getValue($this->owner, $attribute['value']);
        }
    }

    /**
     * The method converts the value of the attributes field to a common form
     * @param array $attributes
     * Example: [
     *     'id',
     *     'login' => 'user_login',
     *     'updated_at' => [
     *         'field' => 'updatedAt',
     *         'value' => 'updated_at',
     *     ],
     *     [
     *         'watch' => 'status',
     *         // 'watch' => ['status', 'username'],
     *         'field' => 'statusName',
     *         'value' => 'attribute',
     *         // 'value' => 'array.key',
     *         // 'value' => ['array', 'key'],
     *         // 'value' => function($owner) {
     *         //     return $owner->array['key'];
     *         // },
     *     ],
     * ]
     */
    public function setAttributes(array $attributes)
    {
        $this->attributes = [];
        foreach ($attributes as $key => $value) {
            if (is_int($key) && is_string($value)) {
                $key = $value;
            }
            $this->attributes[] = [
                'watch' => is_string($key) ? $key : $value['watch'],
                'field' => is_string($value) ? $value : $value['field'],
                'value' => is_string($value) ? $key : $value['value'],
            ];
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
            if ($watch === true) {
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
            } else {
                if (isset($changedAttributes[$watch]) || array_key_exists($watch, $changedAttributes)) {
                    $result[] = $attribute;
                }
            }
        }
        return $result;
    }
}