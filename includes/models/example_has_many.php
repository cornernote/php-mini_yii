<?php
/**
 * Class m_example_has_many
 *
 * @method m_example_has_many findByPk() findByPk(int $pk)
 * @method m_example_has_many find() find(string $where, array $params = null)
 * @method m_example_has_many[] findAll() findAll(string $where, array $params = null)
 *
 * @property int $id
 * @property string $name
 * @property int $example_id
 *
 * @property m_example $example
 */
class m_example_has_many extends mysql_table
{

    /**
     * @param string $className table class name.
     * @return m_example_has_many table model instance.
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
        return 'example_has_many';
    }

    /**
     * @return m_example[]
     */
    public function getExample()
    {
        return m_example::model()->findByPk($this->example_id);
    }

}
