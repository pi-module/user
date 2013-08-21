<?php
namespace Module\User\Controller\Front;

use Pi;
use Pi\Mvc\Controller\ActionController;

class ProfileController extends ActionController
{

    /**
     * User profile page
     * 1. Owner profile view
     * 2. Other profile view
     *
     * @return array|void
     */
    public function indexAction()
    {
        $uid = $this->params('id');
        $isLogin = Pi::service('user')->hasIdentity();

        if (!$uid && !$isLogin) {
            $this->jumpTo404();
        }

        $loginUid = Pi::service('user')->getIdentity();
        if (!$uid || $uid == $loginUid) {
            $uid = Pi::service('user')->getIdentity();
            $isOwner = true;
        }

        // Get display group
        $model  = $this->getModel('display_group');
        $select = $model->select();
        $select->columns('name', 'title', 'order');
        $select->order('order ASC');
        $groups = $model->selectWith($select);
        foreach ($groups as $group) {
            $data[$group->name] = array(
                'name'   => $group->name,
                'title'  => $group->title,
            );

            $compound = $group->compound;
            $model = $this->getModel('field_display');
            $select = $model->select()
                ->where(array('group' => $group->name));
            $select->order('order ASC');
            $fields = $model->selectWith($select);

            foreach ($fields as $field) {
                $data[$group->name]['fields'][$field->name] = array(
                    'name' => $field->field,
                    'order' => $field->order,
                    'value' => Pi::api('user', 'user')->get($field->name, $uid),
                );

                // Profile group
                if (!$compound) {
                    $profileFields = Pi::registry('profile', 'user')->read();
                    if (isset($profileFields[$field->field])) {
                        $title = $profileFields[$field->field]['title'];
                    }
                    $data[$group->name]['fields'][$field->name]['title'] = $title;
                } else {
                    $compoundFields = Pi::registry('compound', 'user')
                        ->read($compound);
                    if (isset($compoundFields[$field->field])) {
                        $title = $data[$group->name]['fields'][$field->name]['title'];
                    }
                    $data[$group->name]['fields'][$field->name]['title'] = $title;
                }
            }
        }

        $this->view()->assign(array(
            'data' => $data,
        ));
    }

    /**
     * User home page
     * 1. Display timeline
     * 2. Display activity link
     *
     * @return array|void
     */
    public function homeAction()
    {
        $page   = $this->params('p', 1);
        $limit  = 10;
        $offset = (int) ($page -1) * $limit;

        $uid = $this->params('uid', '');
        $isLogin = Pi::service('user')->hasIdentity();
        $isOwner = false;

        if (!$uid && !$isLogin) {
            $this->jumpTo404();
        }

        $loginUid = Pi::service('user')->getIdentity();
        if (!$uid || $uid == $loginUid) {
            $uid = Pi::service('user')->getIdentity();
            $isOwner = true;
        }

        // Test display (uid = 1)
        // Get user information

        $user['name'] = Pi::api('user')
            ->getAccount($uid, 'display', array('name'));

        $fields  = array('gender', 'birthdate');
        $profile = PgetProfile($uid, 'display', $fields);
        $user['gender']    = $profile['gender'];
        $user['birthdate'] = $profile['birthdate'];

        // Get timeline
        $count    = Pi::service('user')->timeline($uid)->getCount();
        $timeline = Pi::service('user')->timeline($uid)->get($limit, $offset);

        // Set paginator
        $paginatorOption = array(
            'count' => $count,
            'limit' => $limit,
            'page'  => $page,
            'controller' => 'account',
            'action'     => 'index'
        );
        $paginator = $this->setPaginator($paginatorOption);

        $this->view()->assign(array(
            'user'      => $user,
            'timeline'  => $timeline,
            'paginator' => $paginator,
            'isOwner'   => $isOwner,
        ));
    }

    /**
     * Set paginator
     *
     * @param $option
     * @return \Pi\Paginator\Paginator
     */
    protected function setPaginator($option)
    {
        $paginator = Paginator::factory(intval($option['count']));
        $paginator->setItemCountPerPage($option['limit']);
        $paginator->setCurrentPageNumber($option['page']);
        $paginator->setUrlOptions(array(
            // Use router to build URL for each page
            'pageParam'     => 'p',
            'totalParam'    => 't',
            'router'        => $this->getEvent()->getRouter(),
            'route'         => $this->getEvent()->getRouteMatch()->getMatchedRouteName(),
            'params'        => array(
                'module'        => $this->getModule(),
                'controller'    => $option['controller'],
                'action'        => $option['action'],
            ),
        ));

        return $paginator;
    }

    public function testAction()
    {
        //$result = Pi::api('user', 'user')->get('gender', 1);
        //vd($result);
        //$fields = Pi::registry('profile', 'user')->read(,);
        //$fields = Pi::registry('profile', 'user')->read();
        //vd($fields);
        //$fields = Pi::registry('compound', 'user')->read('work');
        $fields = Pi::registry('profile', 'user')->read();
        if (isset($fields['email'])) {
            vd($fields['email']);
        }
        //vd($fields);
        $this->view()->setTemplate(false);
    }

    /**
     * Edit profile according to group
     */
    public function editAction()
    {
        $group = $this->params('group', '');
        if (!$group) {
            return;
        }

        $isGroup = $this->getModel('display_group')->find($group);
        if (!$isGroup) {
            return;
        }

        $fieldsModel = $this->getModel('field_display');
        $select = $fieldsModel->select()->where(array('group' => $group));
        $select->order('order ASC');
    }
}