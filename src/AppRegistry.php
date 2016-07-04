<?php
/**
 * Created by PhpStorm.
 * User: iespinosa
 * Date: 01/07/2016
 * Time: 12:23
 */

namespace Pheact;

/**
 * Class AppRegistry
 * @package Pheact
 */
class AppRegistry
{
    /**
     * @var JSAppInterface[]
     */
    protected $apps;

    /**
     * Add an application to registry
     * @param JSAppInterface $app
     */
    public function addApplication(JSAppInterface $app)
    {
        $this->apps[$app->getAppName()] = $app;
    }


    /**
     * @param $name
     * @return JSAppInterface
     * @throws \Exception
     */
    public function get($name)
    {
        if(!array_key_exists($name, $this->apps))
            throw new \Exception("pplication {$name} is not registered within the app registry");

        return $this->apps[$name];
    }


    /**
     * @param $class
     * @param $name
     * @return bool
     * @throws \Exception
     */
    public function isApplicationType($class, $name)
    {
        return $this->get($name) instanceof $class;
    }
}