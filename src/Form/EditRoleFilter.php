<?php
/**
 * User system edit role form input filter
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
 * @author          Liu Chuang <liuchuang@eefoucs.com>
 * @since           3.0
 * @package         Module\User
 * @subpackage      Form
 * @version         $Id$
 */

namespace Module\User\Form;

use Pi;
use Zend\InputFilter\InputFilter;

class EditRoleFilter extends InputFilter
{
    public function __construct()
    {
        $this->add(array(
            'name' => 'role',
        ));

        $this->add(array(
            'name'    => 'role_staff',
            'require' => false,
        ));

        $this->add(array(
            'name'    => 'id',
            'require' => false,
        ));
    }
}