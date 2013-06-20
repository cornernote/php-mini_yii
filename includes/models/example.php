<?php
/**
 * Class m_example
 *
 * @method m_example findByPk() findByPk(int $pk)
 * @method m_example find() find(string $where, array $params = null)
 * @method m_example[] findAll() findAll(string $where, array $params = null)
 *
 * @property int $id
 * @property string $name
 *
 * @property m_example_has_many $exampleRelation
 */
class m_example extends mysql_table
{

    /**
     * @param string $className table class name.
     * @return m_example table model instance.
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    /**
     * Name of the database table
     *
     * @return string
     */
    public function getTable()
    {
        return 'example';
    }

    /**
     * @return m_example_has_many[]
     */
    public function getExampleHasMany()
    {
        return m_example_has_many::model()->findAll('example_id=:id', array(
            ':id' => $this->id,
        ));
    }

}
