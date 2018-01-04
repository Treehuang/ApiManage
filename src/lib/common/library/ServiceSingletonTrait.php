<?php
/**
 * @author index
 */

namespace common\library;


trait ServiceSingletonTrait
{
    use ServiceBaseTrait;
    use SingletonTrait;

    /**
     * Execute service method
     *
     * @param string $name Service method name
     * @param array $arguments Service method parameters
     * @return mixed Service method result
     */
    final public static function __callStatic($name, $arguments) {

        return self::getInstance()->_callServiceMethod($name, $arguments);
    }

    public static function getLastCode() {
        return self::getInstance()->_lastCode;
    }
}