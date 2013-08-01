<?php
/**
 * User system module navigation config
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
 * @author          Liu Chuang <taiwenjiang@tsinghua.org.cn>
 * @since           3.0
 * @package         Module\User
 * @version         $Id$
 */

return array(
    'front'   => array(
    ),
    'admin' => array(
        'form' => array(
            'label'         => _t('Form'),
            'resource'      => array(
                'resource'  => 'module',
            ),
            'route'         => 'admin',
            'module'        => 'user',
            'controller'    => 'custom',
            'action'        => 'register',
        ),
    ),
);