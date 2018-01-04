<?php
/**
 * @author index
 */

namespace common\library;


trait ServiceTrait
{
    use ServiceBaseTrait;

    /**
     * Execute service method.
     *
     * @param string $name Service method name
     * @param array $arguments Service method parameters
     * @return mixed Service method result
     */
    final public function __call($name, $arguments) {

        return $this->_callServiceMethod($name, $arguments);
    }

    /**
     * Get the last return code.
     *
     * @return int return code
     */
    public function getLastCode() {
        return $this->_lastCode;
    }
}