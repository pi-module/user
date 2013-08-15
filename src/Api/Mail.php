<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\User\Api;

use Pi;
use Pi\Application\AbstractApi;

/**
 * User password manipulation APIs
 *
 * @author Liu Chuang <liuchuang@eefocus.com>
 */
class Mail extends AbstractApi
{
    protected $module = 'user';

    public function setData($uid, $name, $content, $module = null)
    {
        $module = $module ? $module : 'user';

        $model = Pi::model('data', $this->module);
        $where = array(
            'uid'  => $uid,
            'name' => $name,
        );

        $seletct = $model->select()->where($where);
        $row = $model->selectWith($seletct)->current();

        if (!$row->id) {
            // Insert a new data
            $row = $model->createRow(array(
                'uid' => $uid,
                'module' => $module,

            ));
        }

    }

    protected function getSmtpOptions()
    {

    }
}
