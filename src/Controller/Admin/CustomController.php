<?php
/**
 * User module custom controller
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       Copyright (c) Copyright (c) Pi Engine http://www.xoopsengine.org
 * @license         http://www.xoopsengine.org/license New BSD License
 * @author          Liu Chuang <liuchuang@eefocus.com>
 * @since           1.0
 * @package         Module\User
 */

namespace Module\User\Controller\Admin;

use Pi\Mvc\Controller\ActionController;
use Pi;
use Module\User\Form\RegisterForm;

/**
 * Custom action controller for administrator custom user system.
 */
class CustomController extends ActionController
{
    /**
     * Custom register form preview.
     */
    public function registerFormAction()
    {
        // Get custom configs from config file.
        $configFile = sprintf('%s/%s/config/custom.register.form.php', Pi::path('module'), $this->getModule());
        $configs = include $configFile;

        if (!empty($configs)) {

            // Get group.
            $groups = array();
            foreach ($configs['category'] as $category) {
                $groups[$category['name']] = array(
                    'label'    => $category['title'],
                    'elements' => array(),
                );
            }

            foreach ($configs['item'] as $item) {
                $groups[$item['category']]['elements'][] = $item['element']['name'];
            }

            $form = $this->getForm($configs['item']);
            $form->setGroups($groups);
        }
        $this->view()->assign('form', $form);
        $this->view()->setTemplate('custom-form');
    }

    /**
     * Get custom form to preview
     *
     * @param $configs
     * @return \Module\User\Form\RegisterForm
     */
    protected function getForm($configs)
    {
        $form = new RegisterForm($configs);
        return $form;
    }

}