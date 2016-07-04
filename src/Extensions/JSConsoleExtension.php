<?php
/**
 * Created by PhpStorm.
 * User: iespinosa
 * Date: 01/07/2016
 * Time: 14:35
 */

namespace Pheact\Extensions;

/**
 * Class JSConsoleExtension
 * @package Pheact\Extensions
 */
class JSConsoleExtension extends JSExtension
{
    /**
     * final name
     *
     * @return string
     */
    public function getName()
    {
        return 'console';
    }

    /**
     * Active by default
     *
     * @return bool
     */
    public function isEnabled()
    {
        return true;
    }


    /**
     * @return string
     */
    public function getCode()
    {
        return <<< EOT
            
            var console  = {
                log: function(){},
                warn: print,
                error: print,
                exception: print
            };

EOT;

    }

}