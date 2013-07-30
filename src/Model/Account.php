<?php
namespace Module\User\Model;

use Pi\Application\Model\Model;
use Pi;

/**
 * User system account model
 *
 * @author Liu Chuang <liuchuang@eefocus.com>
 */
class Account extends Model
{
    /**
     * Prepare insert data to account table
     *
     * @param $data
     * @return \Pi\Db\Table\Row|\Pi\Db\Table\RowGateway
     */
    public function prepare($data)
    {
        $data['salt'] = $this->createSalt();
        $data['credential'] = $this->transformCredential($data['credential'], $data['salt']);
        return $this->createRow($data);
    }

    /**
     * Produce salt
     *
     * @return string
     */
    public function createSalt()
    {
        return uniqid(mt_rand(), 1);
    }

    /**
     * Transform credential
     *
     * @param $credential
     * @param $salt
     * @return string
     */
    public function transformCredential($credential, $salt)
    {
        $credential = md5(sprintf('%s%s%s', $salt, $credential, Pi::config('salt')));
        return $credential;
    }

    /**
     * Get identity column
     *
     * @return string
     */
    public function getIdentityColumn()
    {
        return 'identity';
    }
}