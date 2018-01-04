<?php
/**
 * @author index
 */

namespace common\library;


use common\core\Configure;

/**
 * Class LDAPService
 * @package common\library
 *
 * @method static array|bool verify($userDN, $password)
 * @method static string searchFirstDN($rootDN, $searchBase, $filter, $managerDN = null, $managerPassword = null)
 */
class LDAPService
{
    use ServiceSingletonTrait;

    /**
     * If multiple instances service.
     *
     * @return bool true: multiple instances; false: otherwise
     */
    public function _isMultiService()
    {
        return Configure::get('ldap.multi', false);
    }

    /**
     * Get service name.
     *
     * @return string
     */
    public function _getServiceName()
    {
        return Configure::get('ldap.name', 'data.ldap');
    }

    /**
     * Get ServiceEngine instance.
     *
     * @param array $serviceList service instance list
     * @return ServiceEngineTrait ServiceEngine instance
     */
    public function _getServiceEngine($serviceList)
    {
        return new LDAPServiceEngine($serviceList);
    }
}