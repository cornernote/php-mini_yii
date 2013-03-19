<?php
/**
 * Class m_example
 *
 * @method m_example findByPk() findByPk(int $pk)
 * @method m_example find() find(string $where, array $params = null)
 * @method m_example[] findAll() findAll(string $where, array $params = null)
 *
 * @property $id int
 * @property $name string
 * @property $some_relation_id int
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
     * @return m_some_relation[]
     */
    public function getSomeRelation()
    {
        if ($data = $this->getCache('getSomeRelation')) return $data;
        return $this->setCache('getSomeRelation', m_some_relation::model()->findAll('id=:some_relation_id', array(
            ':some_relation_id' => $this->some_relation_id,
        )));
    }

}
