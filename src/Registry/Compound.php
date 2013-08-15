<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @package         Registry
 */

namespace Module\User\Registry;

use Pi;
use Pi\Application\Registry\AbstractRegistry;

/**
 * Pi user profile compound field registry
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Compound extends AbstractRegistry
{
    /** @var string Module name */
    protected $module = 'user';

    /**
     * {@inheritDoc}
     */
    protected function loadDynamic($options = array())
    {
        $fields = array();

        $columns = array('name', 'title', 'compound', 'edit', 'filter');
        $where = array('active' => 1);
        $model = Pi::model('compound_field', $this->module);
        $select = $model->select()->where($where)->columns($columns);
        $rowset = $model->selectWith($select);
        foreach ($rowset as $row) {
            $fields[$row->name] = $row->toArray();
        }

        return $fields;
    }

    /**
     * {@inheritDoc}
     * @param string $compound Compound name: tool, address, education, wrok
     * @param string $action Actions: display, edit, search
     * @param array
     */
    public function read($compound = '')
    {
        $options = array();
        $data = $this->loadData($options);
        $result = isset($data[$compound]) ? $data[$compound] : array();

        return $result;
    }

    /**
     * {@inheritDoc}
     * @param string $compound
     */
    public function create($compound = '')
    {
        $this->clear('');
        $this->read($compound);

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function setNamespace($meta = '')
    {
        return parent::setNamespace('');
    }

    /**
     * {@inheritDoc}
     */
    public function flush()
    {
        return $this->clear('');
    }
}
