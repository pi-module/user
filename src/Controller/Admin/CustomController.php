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
     * Custom register form preview.
     */
    public function registerAction()
    {
        // Get custom configs from config file.
        $configFile = sprintf('%s/extra/%s/config/custom.register.form.php', Pi::path('usr'), $this->getModule());
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

            $form = $this->getForm('register', $configs['item']);
            $form->setGroups($groups);
        }
        $this->view()->assign(array(
            'form' => $form,
            'type' => 'register',
        ));
        $this->view()->setTemplate('custom-form');
    }

    public function registerCompleteAction()
    {
        // Get custom configs from config file.
        $configFile = sprintf('%s/extra/%s/config/custom.register.complete.form.php', Pi::path('usr'), $this->getModule());
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

            $form = $this->getForm('registerComplete', $configs['item']);
            $form->setGroups($groups);

            // Set profile field
            $categoryList = $configs['category'];
            $this->setProfileCategory($categoryList);
            $this->setProfile_field($configs['item']);

        }
        $this->view()->assign(array(
            'form' => $form,
            'type' => 'complete',
        ));
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
     * Get table field
     *
     * @param $tableName
     * @return array
     */
    protected function getTableColumsName($tableName)
    {

        $rowset = $this->getModel($tableName)->getColumnsName();
        foreach ($rowset as $row) {
            $result[] = $row['name'];
        }
        return $result;
    }

    /**
     * Get category map from custom config
     *
     * @param $items
     * @return array
     */
    protected function getCategoryMap($items)
    {
        foreach ($items as $item) {
            $category[$item['element']['name']] = $item['category'];
        }

        return $category;
    }

    /**
     * Set profile category from custom form config
     *
     * @param $category
     */
    protected function setProfileCategory($category)
    {
        if ($category) {
            $profileCategoryModel = $this->getModel('profile_category');
            foreach ($category as $item) {
                $row = $profileCategoryModel->find($item['name'], 'name');
                if (!$row) {
                    // Add profile category.
                    $data = array(
                        'name'  => $item['name'],
                        'title' => $item['title'],
                    );

                    $row = $profileCategoryModel->createRow($data);
                    $result = $row->save();
                } else {
                    // Update profile category
                    $row->name  = $item['name'];
                    $row->title = $item['title'];
                    $row->save();
                }
            }
        }
    }

    /**
     * Set profile field item from custom  form config
     *
     * @param $config
     */
    protected function setProfile_field($config)
    {
        $categoryMap = $this->getCategoryMap($config);
        $profile_fieldModel = $this->getModel('profile_field');
        $module = $this->getModule();

        foreach ($categoryMap as $name => $category) {
            $select = $profile_fieldModel->select()->where(array('name' => $name, 'module' => $module));
            $row = $profile_fieldModel->selectWith($select)->current();

            if (!$row) {
                // Insert profile field
                $data = array(
                    'module'   => $this->getModule(),
                    'name'     => $name,
                    'category' => $category,
                );

                $row = $profile_fieldModel->createRow($data);
                $row->save();
            } else {
                // Update
                $row->name = $name;
                $row->category = $category;
                $row->save();
            }
        }
    }
}