<?php
/**
 * Edit Role form
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       Copyright (c) Pi Engine http://www.xoopsengine.org
 * @license         http://www.xoopsengine.org/license New BSD License
 * @author          Liu Chuang <liuchuang@eefocus.com>
 * @since           3.0
 * @package         Module\User
 * @subpackage      Form
 * @version         $Id$
 */

namespace Module\User\Form;

use Pi;
use Pi\Form\Form as BaseFrom;

class EditRoleForm extends BaseFrom
{
    protected $user = array(
        'role'       => 'member',
        'role_staff' => '',
    );

    public function __construct($name = null, $user = array())
    {
        $this->user = array_merge($this->user, $user);
        parent::__construct($name);
    }

    public function init()
    {
        // Edit role
        $this->add(array(
            'name' => 'role',
            'type' => 'role',
            'options' => array(
                'label' => __('User role'),
            ),
            'attributes' => array(
                'value' => $this->user['role'],
            ),
        ));

        // Edit staff
        $this->add(array(
            'name' => 'role_staff',
            'type' => 'role',
            'options' => array(
                'label' => __('Management role'),
                'section' => 'admin',
            ),
            'attributes' => array(
                'value' => $this->user['role_staff'],
            ),
        ));

        $this->add(array(
            'name'  => 'security',
            'type'  => 'csrf',
        ));

        $this->add(array(
            'name' => 'id',
            'attributes' => array(
                'type' => 'hidden',
                'value' => $this->user['id'],
            ),
        ));

        $this->add(array(
            'name'       => 'redirect',
            'attributes' => array(
                'type'  => 'hidden',
                'value' => $this->user['redirect'],
            ),
        ));
        $this->add(array(
            'name'          => 'submit',
            'type'          => 'submit',
            'attributes'    => array(
                'value' => __('Submit'),
            ),
        ));
    }
}