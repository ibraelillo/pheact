<?php
/**
 * Created by PhpStorm.
 * User: iespinosa
 * Date: 01/07/2016
 * Time: 14:47
 */

namespace Pheact;

/**
 * Class ReactJSApplication
 * @package Pheact
 */
class ReactJSApplication extends JSApplication
{

    /**
     * @var bool
     */
    protected $isPackaged = false;

    /**
     * @var string
     */
    protected $script;

    /**
     * @var array
     */
    protected $registerVariables = [];



    public function renderView($view, $state)
    {
        $initialState = [
            'location' => $view
        ];

        $data = json_encode($state);
        $script = "\nwindow.___INITIAL_STATE__ = {$data};";

        $this->executeScript(
            $this->compileString($script. file_get_contents($this->script))
        );
    }

    /**
     * @param $state
     * @throws \V8JsScriptException
     */
    public function renderAppWithState($state)
    {
        $data = json_encode($state);
        $script = "\nwindow.___INITIAL_STATE__ = {$data};";

        $this->executeScript(
            $this->compileString($script. file_get_contents($this->script))
        );
    }
    
    
    /**
     *
     */
    public function boot()
    {

        parent::boot();


        if($this->isPackaged)
        {
            foreach ($this->registerVariables as $var)
            {
                $this->execute("var {$var} = {$this->appName}.{$var};");
            }
        }

    }

    /**
     * Add register variables
     *
     * @param $variables
     * @return $this
     */
    public function addRegisterVariables($variables)
    {
        array_merge($this->registerVariables, $variables);

        return $this;
    }

    /**
     * @param $name
     * @param $value
     * @return $this
     */
    public function addRegisterVariable($name, $value)
    {
        $this->registerVariables[$name] = $value;

        return $this;
    }


    /**
     * @return boolean
     */
    public function mustReportExceptions()
    {
        return $this->repost_exceptions;
    }

    /**
     * @param boolean $repost_exceptions
     * @return JSApplication
     */
    public function setReportExceptions($report_exceptions)
    {
        $this->report_exceptions = $report_exceptions;
        return $this;
    }
    
    

}