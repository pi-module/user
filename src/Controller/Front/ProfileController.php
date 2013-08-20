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
        $uid = $this->params();
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
        $rowset = $model->selectWith($select);
        foreach ($rowset as $row) {
            $group[$row->name] = array(
                'title'  => $row->title,
                'fields' => array(),
            );
        }

        // Get display field
        foreach (array_keys($group) as $groupName) {
            $model  = $this->getModel('field_display');
            $select = $model->select()->where(array('group' => $groupName));
            $rowset = $model->selectWith($select);
            //$fields =
        }

        //$rowset =







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
        //$result = Pi::api('user', 'user')->getMeta();
        // $result = Pi::registry('profile', 'user')->read();
        //$result = Pi::api('user', 'user')->canonizeMeta(array('work', 'tool', 'signature'));
        //$result = Pi::model('account', 'user');
        //$result = $this->getModel('account');
        $result = Pi::api('user', 'user')->getMeta();
        //$result = Pi::registry('profile', 'user')->read('', 'search');
        d($result);

        $this->view()->setTemplate(false);
    }
}