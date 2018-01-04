<?php
/**
 * @author index
 */

namespace common\library;

/**
 * Class ServiceEngineTrait
 * @package common\library
 */
trait ServiceEngineTrait
{
    /** @var array $_serviceList service info list */
    protected $_serviceList;
    
    /** @var int $_lastCode the last return code */
    protected $_lastCode;
    
    /** @var string $_lastMethod the last method name */
    protected $_lastMethod;

    /**
     * ServiceEngineTrait constructor.
     * @param array $serviceList
     */
    final public function __construct($serviceList)
    {
        $this->_serviceList = $serviceList;

        $this->_init();
    }

    /**
     * @return mixed
     */
    abstract protected function _init();

    /**
     * @return int
     */
    public function getLastCode()
    {
        return $this->_lastCode;
    }

    /**
     * @param int $lastCode
     */
    public function setLastCode($lastCode)
    {
        $this->_lastCode = $lastCode;
    }

    /**
     * @return string
     */
    public function getLastMethod()
    {
        return $this->_lastMethod;
    }

    /**
     * @param string $lastMethod
     */
    public function setLastMethod($lastMethod)
    {
        $this->_lastMethod = $lastMethod;
    }
}