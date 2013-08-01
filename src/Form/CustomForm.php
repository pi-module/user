<?php
/**
 * Register form
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

class CustomForm extends BaseForm
{
    protected $configs;
    protected $name;

    /**
     * Constructor
     * @param null $configs
     */
    public function __construct($name, $configs)
    {
        $this->configs = $configs;
        $this->name    = $name;
        parent::__construct($this->name);
    }

    public function init()
    {
        foreach ($this->configs as $config) {
            $this->add($config['element']);
        }

        $this->add(array(
            'name' => 'submit',
            'type' => 'submit',
            'attributes' => array(
                'value' => __('Submit'),
            )
        ));
    }

//    /**
//     * Verify form
//     * @return bool
//     */
//    public function isValid()
//    {
//        foreach ($this->configs as $filter) {
//            if (!empty($filter['filter'])) {d($filter['filter']);
//                $this->getInputFilter()->add($filter['filter']);
//            }
//        }
//        return parent::isValid();
//    }
}