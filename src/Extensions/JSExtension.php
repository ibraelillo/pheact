<?php
/**
 * Created by PhpStorm.
 * User: iespinosa
 * Date: 01/07/2016
 * Time: 12:31
 */

namespace Pheact\Extensions;

/**
 * Class JSExtension
 * @package Pheact\Extensions
 */
class JSExtension
{

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $code;

    /**
     * @var  JSExtension[]
     */
    protected $dependencies = [];


    /**
     * @var bool
     */
    protected $enabled;

    /**
     * JSExtension constructor.
     * @param string $name
     * @param string $code
     * @param JSExtension[] $dependencies
     * @param bool $enabled
     */
    public function __construct($name = null, $code = null, array $dependencies = [], $enabled = false)
    {
        $this->name = $name;
        $this->code = $code;
        $this->dependencies = $dependencies;
        $this->enabled = $enabled;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
    }

    /**
     * @return array
     */
    public final function toCompatibleArray()
    {
        return [
            $this->getName(),
            $this->getCode(),
            array_map(function($ext){ return $ext instanceof JSExtension ?  $ext->getName() : $ext; }, $this->dependencies),
            $this->isEnabled()
        ];
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return JSExtension
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $code
     * @return JSExtension
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @return JSExtension[]
     */
    public function getDependencies()
    {
        return $this->dependencies;
    }

    /**
     * @param JSExtension[] $dependencies
     * @return JSExtension
     */
    public function setDependencies($dependencies)
    {
        $this->dependencies = $dependencies;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param boolean $enabled
     * @return JSExtension
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
        return $this;
    }
}