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
use Module\User\Form\CustomForm;

/**
 * Custom action controller for administrator custom user system.
 */
class CustomController extends ActionController
{
    /**
     * Read profile field config and store to database
     */
    public function configAction()
    {
        $file    = sprintf('%s/extra/%s/config/profile.field.php', Pi::path('usr'), $this->getModule());
        $configs = include $file;
        $field = array();
        if (!empty($configs)) {
            foreach ($configs as $config) {
                if ($config['element']['name']) {
                    $name = $config['element']['name'];
                    $field[$name]['element'] = serialize($config['element']);
                    $field[$name]['filter']  = serialize($config['filter']);
                    $field[$name]['name']    = $config['element']['name'];
                    $field[$name]['title']   = $config['element']['options']['label'];
                    $field[$name]['value']   = isset($config['element']['attribute']['value']) ?: '';
                }
            }
        }

        // Insert to database
        if (!empty($field)) {
            $model = $this->getModel('profile_config');
            foreach ($field as $data) {
                $row = $model->createRow($data);
                $row->save();
            }
        }

        $this->view()->setTemplate(false);
    }

    /**
     * Custom register form preview.
     */
    public function registerAction()
    {
        // Get custom configs from config file.
        $configFile = sprintf('%s/extra/%s/config/custom.register.form.php', Pi::path('usr'), $this->getModule());
        $configs = include $configFile;

        $baseColumns = $this->getBaseFieldColumns();
        $formElement = array();
        foreach ($configs as $config) {
            if (isset($config['name'])) {
                $row = $this->getModel('profile_config')->find($config['name'], 'name');
                if ($row) {
                    $formElement[] = array(
                        'element' => unserialize($row->element),
                        'filter'  => $row->filter ? unserialize($row->filter) : array(),

                    );
                }
            } elseif (isset($config['element']) && isset($config['filter'])) {
                if (in_array($config['element']['name'], $baseColumns)) {
                    $formElement[] = $config;
                }
            }
        }

        if (!empty($formElement)) {
            $form = $this->getForm('register', $formElement);

            $this->view()->assign(array(
                'form' => $form,
                'type' => 'register',
            ));
        }

        $this->view()->setTemplate('custom-form');
    }

    public function registerCompleteAction()
    {
        // Get custom configs from config file.
        $configFile = sprintf('%s/extra/%s/config/custom.register.complete.form.php', Pi::path('usr'), $this->getModule());
        $configs = include $configFile;
        $baseColumns = $this->getBaseFieldColumns();
        $formElement = array();
        foreach ($configs as $config) {
            if (isset($config['name'])) {
                $row = $this->getModel('profile_config')->find($config['name'], 'name');
                if ($row) {
                    $formElement[] = array(
                        'element' => unserialize($row->element),
                        'filter'  => $row->filter ? unserialize($row->filter) : array(),

                    );
                }
            } elseif (isset($config['element']) && isset($config['filter'])) {
                if (in_array($config['element']['name'], $baseColumns)) {
                    $formElement[] = $config;
                }
            }
        }

        if (!empty($formElement)) {
            $form = $this->getForm('register.complete', $formElement);

            $this->view()->assign(array(
                'form' => $form,
                'type' => 'complete',
            ));
        }
        $this->view()->setTemplate('custom-form');
    }

    /**
     * Get custom form to preview
     *
     * @param $configs
     * @return \Module\User\Form\CustomForm
     */
    protected function getForm($name, $configs)
    {
        $form = new CustomForm($name, $configs);
        return $form;
    }

    /**
     * Get base field columns from config file
     * @return mixed
     */
    protected function getBaseFieldColumns()
    {
        $file = sprintf('%s/extra/%s/config/base.field.columns.php', Pi::path('usr'), $this->getModule());
        $columns = include $file;

        return $columns;
    }
}