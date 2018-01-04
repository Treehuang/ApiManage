<?php

/**
 * 加盐哈希
 */
 
namespace common\library;

define('SALT_BYTE_SIZE', 24);

class SaltHash {

    /**
     * 生成哈希密码
     *
     * @param string $password 待加密密码
     * @param string &$salt 生成的盐值
     * @param string &$hashPassword 加密后的密码
     * @return bool
     */
    public static function getHash($password, &$salt, &$hashPassword)
    {
        $crypto_strong = true;
        $salt = base64_encode(openssl_random_pseudo_bytes(SALT_BYTE_SIZE, $crypto_strong));
        $toHash = $password . $salt;
        $hashPassword = sha1($toHash);
        return true;
    }

    /**
     * 验证密码
     *
     * @param string $password 原密码
     * @param string $salt 盐值
     * @param string $hashPassword 待比较的加密后密码
     * @return bool 通过: true, 不通过: false
     */
    public static function validate($password, $salt, $hashPassword)
    {
        $toHash = $password . $salt;
        $hashPasswordNew = sha1($toHash);
        return $hashPasswordNew == $hashPassword;
    }

    /**
     * [GenSimpleHash 获取普通哈希码]
     *
     * @param mixed $data 数据
     * @param string &$hashCode 哈希码
     * @return bool
     */
    public static function generateSimpleHash($data, &$hashCode)
    {
        $hashCode = hash('sha1', $data);
        return true;
    }




}
 
