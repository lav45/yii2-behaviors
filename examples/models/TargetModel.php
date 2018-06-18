<?php
/**
 * Created by PhpStorm.
 * User: and1
 * Date: 18.06.2018
 * Time: 15:15
 */

use yii\base\Model;

/**
 * Class TargetModel
 */
class TargetModel extends Model
{
    /**
     * @var int
     */
    public $id;
    /**
     * @var string
     */
    public $name;
    /**
     * @var string
     */
    public $dn;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'required'],
            [['id'], 'integer'],

            [['name'], 'required'],
            [['name'], 'string'],

            [['dn'], 'required'],
            [['dn'], 'string'],
        ];
    }

    /**
     * @return bool
     */
    public function save()
    {
        if ($this->validate() === false) {
            return false;
        }

        $data = array_filter([
            'DepartmentID' => $this->id,
            'ou' => $this->name,
            'dn' => $this->dn,
            'objectClass' => ['top', 'organizationalUnit'],
        ]);

        return $this->saveEntry('ou', $data);
    }

    /**
     * @return bool
     */
    public function delete()
    {
        return $this->deleteEntry('DepartmentID', $this->id);
    }

    /**
     * @param string $attribute
     * @param array $data
     */
    private function saveEntry($attribute, $data)
    {
        //Save data
    }

    /**
     * @param string $attribute
     * @param int $value
     */
    private function deleteEntry($attribute, $value)
    {
        //Delete data
    }
}