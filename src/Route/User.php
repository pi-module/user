<?php
/**
 * User module custom route
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
 * @author          Liu Chuang <zongshu@eefocus.com>
 * @since           1.0
 * @package         Module\User
 * @subpackage      Route
 */

namespace Module\User\Route;

use Pi\Mvc\Router\Http\Standard;
use Zend\Mvc\Router\Http\RouteMatch;
use Zend\Stdlib\RequestInterface as Request;
use Pi;

class User extends Standard
{
    protected $prefix = '';

    /**
     * Default values.
     *
     * @var array
     */
    protected $defaults = array(
        'module'        => 'user',
        'controller'    => 'register',
        'action'        => 'index'
    );

    /**
     * match(): defined by Route interface.
     *
     * @see    Route::match()
     * @param  Request $request
     * @return RouteMatch
     */
    public function match(Request $request, $pathOffset = null)
    {
        $result = $this->canonizePath($request, $pathOffset);
        if (null === $result) {
            return null;
        }
        list($path, $pathLength) = $result;
        $path    = trim($path, $this->paramDelimiter);
        $matches = array();

        if (empty($path)) {
            return null;
        } else {
            $path   = trim($path, $this->paramDelimiter);
            $params = explode('/', $path);
            $first  = isset($params[0]) ? $params[0] : null;
            $second = isset($params[1]) ? $params[1] : null;
            $third  = isset($params[2]) ? $params[2] : null;
            $fourth = isset($params[3]) ? $params[3] : null;

            if ('user' === $first) {
                unset($params[0]);
                if ('register' === $second) {
                    $matches['controller'] = $second;
                    unset($params[1]);

                    if ('complete' == $third) {

                    } elseif ('activate' == $third) {

                    } else {
                        $matches['action'] = 'index';
                    }
                }
            } else {
                return null;
            }
            if (!empty($params)) {
                return null;
            }
        }

        return new RouteMatch(array_merge($this->defaults, $matches), $pathLength);
    }
}