<?php
/**
 * Created by PhpStorm.
 * User: iespinosa
 * Date: 01/07/2016
 * Time: 12:24
 */

namespace Pheact;

/**
 * Interface JSAppInterface
 * @package Pheact
 */
interface JSAppInterface
{

    /**
     * Get the app name
     *
     * @return string
     */
    public function getAppName();

    /**
     * Execute an script
     *
     * @return mixed
     */
    public function executeScript($code, $name = null);

}