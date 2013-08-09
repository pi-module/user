<?php
/**
 * Edit Password form
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
use Pi\Form\Form as BaseForm;

class EditPasswordForm extends BaseForm
{
    protected $user = array();

    public function __construct($name = null, $user = array())
    {
        $this->user = array_merge($this->user, $user);
        parent::__construct($name);
    }

    public function init()
    {
        // New password
        $this->add(array(
            'name'      => 'credential',
            'options'   => array(
                'label' => __('Password'),
            ),
            'attributes' => array(
                'type' => 'password',
            )
        ));

        $this->add(array(
            'name'       => 'credential-confirm',
            'options'    => array(
                'label' => __('Repeat password'),
            ),
            'attributes' => array(
                'type' => 'password',
            ),
        ));

        $this->add(array(
            'name' => 'security',
            'type' => 'csrf',
        ));

        $this->add(array(
            'name'       => 'identity',
            'attributes' => array(
                'type' => 'hidden',
                'value' => $this->user['id'],
            ),
        ));

        $this->add(array(
            'name'       => 'submit',
            'type'       => 'submit',
            'attributes' => array(
                'value' => __('Submit'),
            ),
        ));
    }
}