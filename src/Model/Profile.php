<?php
namespace Module\User\Model;

use Pi\Application\Model\Model;
use Pi;

/**
 * User system profile model
 *
 * @author Liu Chuang <liuchuang@eefocus.com>
 */
class Profile extends Model
{
    public function getColumnsName()
    {
        $table    = $this->getTable();
        $database = Pi::config()->load('service.database.php');
        $schema   = $database['schema'];
        $sql      = 'select COLUMN_NAME as name from information_schema.columns where table_name=\'' . $table . '\' and table_schema=\'' . $schema . '\'';
        try {
            $rowset = Pi::db()->getAdapter()->query($sql, 'prepare')->execute();
        } catch (\Exception $exception) {
            return false;
        }

        return $rowset;

    }
}