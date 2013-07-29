<?php
namespace Module\User\Model;

use Pi\Application\Model\Model;
use Pi;

class Account extends Model
{
    public function prepare($data)
    {
        $data['salt'] = $this->createSalt();
        $data['identity'] = $this->transformCredential($data['identity'], $data['salt']);
        return $this->createRow($data);
    }


    public function authenticate($data)
    {
        $row = $this->find($data['username'], 'username');
        if ($row->password == md5($data['password'])) {
            return $row;
        }

        return false;
    }

    public function getList($where, $columns, $order)
    {
        $select = $this->select()
            ->columns($columns)
            ->where($where)
            ->order($order);
        $rowset = $this->selectWith($select);

        return $rowset;
    }

    public function createSalt()
    {
        return uniqid(mt_rand(), 1);
    }

    public function transformCredential($credential, $salt)
    {
        $credential = md5(sprintf('%s%s%s', $salt, $credential, Pi::config('salt')));
        return $credential;
    }
}