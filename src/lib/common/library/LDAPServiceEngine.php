<?php
/**
 * @author index
 */

namespace common\library;


use common\core\Configure;

class LDAPServiceEngine
{
    use ServiceEngineTrait;

    /** @var resource LDAP连接 */
    protected $_ldap;

    protected $_config;

    /**
     * 用户验证方法
     *
     * @param string $userDN userDN
     * @param string $password 密码
     * @return array|bool 认证成功, 返回账号信息, 失败放回false
     */
    public function verify($userDN, $password)
    {
        $pass = @ldap_bind($this->_ldap, $userDN, $password);
        // 处理验证错误
        if (!$pass) {

            //return "kinming: bind fail";

            Log::warning('ldap_bind(): bind user failed', [$userDN, substr($password, 1, 3)]);
            return false;
        }
        // 记录验证通过
        Log::debug('ldap_bind(): bind user success', [$userDN, $password]);
        // 读取账号信息
        $userField = Configure::get('ldap_auth.username', 'cn');
        $orgField = Configure::get('ldap_auth.org', 'org');
        $resource = ldap_search($this->_ldap, $userDN, "(&)");
        $entry = ldap_first_entry($this->_ldap, $resource);
        $info = ldap_get_attributes($this->_ldap, $entry);

        // 获取用户名
        if (!isset($info[$userField])) {
            Log::error("LDAP account info: $userField not set.");
            return false;
        }
        $account['name'] = $info[$userField][0];

        // 获取org
        if (is_numeric($orgField)) {
            $account['org'] = intval($orgField);
        }
        elseif (isset($info[$orgField])) {
            $account['org'] = intval($info[$orgField][0]);
        }
        else {
            Log::error("LDAP account info: $orgField not set.");
            return false;
        }

        return $account;
    }

    /**
     * 搜索DN, 返回第一个结果
     *
     * @param string $rootDN
     * @param string $searchBase
     * @param string $filter
     * @param string|null $managerDN
     * @param string|null $managerPassword
     * @return string dn
     */
    public function searchFirstDN($rootDN, $searchBase, $filter, $managerDN = null, $managerPassword = null)
    {

        // 管理员登录
        if (!is_null($managerDN) && !is_null($managerPassword) && !@ldap_bind($this->_ldap, $managerDN, $managerPassword)) {
            Log::warning("ldap_bind(): bind manager failed:", [$managerDN, substr($managerPassword, 1, 3)]);
            $this->_lastCode = ReturnCode::PERMISSION_ERROR;
            return null;
        }

        // 搜索
        $searchResult = @ldap_search($this->_ldap, $searchBase . ',' . $rootDN, $filter);
        if ($searchResult) {
            $this->_lastCode = 0;
            if (ldap_count_entries($this->_ldap, $searchResult) > 0) {
                $dn = ldap_get_dn($this->_ldap, ldap_first_entry($this->_ldap, $searchResult));
                Log::debug('ldap_get_dn(): dn: ' . $dn);
                ldap_close($this->_ldap);
                return $dn;
            } else {
                $this->_lastCode = ReturnCode::LOGICAL_NOT_FOUND;
                Log::debug('ldap_count_entries(): 0', [$rootDN, $searchBase, $filter]);
            }
        } else {
            $this->_lastCode = ReturnCode::LOGICAL_NOT_FOUND;
            Log::warning('ldap_search(): Search: No such object', [$rootDN, $searchBase, $filter]);
        }

        ldap_close($this->_ldap);
        return null;
    }

    /**
     * 初始化方法
     *
     * @access protected
     * @return bool
     */
    protected function _init()
    {
        $this->_ldap = ldap_connect('ldap://' . $this->_serviceList[0]['ip'], $this->_serviceList[0]['port']);
        ldap_set_option($this->_ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($this->_ldap, LDAP_OPT_REFERRALS, 0);
        $this->_lastCode = 0;
    }
}