<?php
/**
 * Created by PhpStorm.
 * User: iespinosa
 * Date: 30/06/2016
 * Time: 15:27
 */

namespace Pheact;
use Pheact\Extensions\JSExtension;
use Psr\Log\LoggerInterface;

/**
 *  This class intend to execute javascript code after doing some booting in a v8 javascript engine.
 *  Compatible with javascript frameworks like react, handlebars, jquery ou angular. 
 *
 *
 *
 * Class JSApplication
 * @package Pheact
 */
class JSApplication
{

    /**
     * @var string
     */
    protected $logger;

    /**
     * @var \V8Js
     */
    protected $engine;

    /**
     * @var string
     */
    protected $appName;

    /**
     * @var
     */
    protected $appCode;

    /**
     * @var array
     */
    protected $helpers;

    /**
     * @var array
     */
    protected $decorators = [];

    /**
     * @var
     */
    protected $scripts = [];

    /**
     * @var bool
     */
    protected $report_exceptions;

    /**
     * @var JSExtension[]
     */
    protected $extensions = [];

    /**
     * @param $name
     * @param array $helpers
     * @param array $decorators
     * @param bool $report_exceptions
     * @return static
     */
    public static function create($name, array $helpers = [], array $decorators = [], $report_exceptions = false)
    {
        return new static($name, $helpers, $decorators, $report_exceptions);
    }

    public function __toString()
    {
        return $this->appName;
    }

    /**
     * @param $globalName
     * @param $jsCode
     * @param array $dependences
     * @param bool $autoEnable
     */
    public static function register($globalName, $jsCode, array $dependences = [], $autoEnable = false)
    {
        \V8Js::registerExtension($globalName, $jsCode, $dependences, (bool)$autoEnable);
    }


    /**
     * Initialize by passing JS code as a string.
     * The application source code is concatenated string
     * of all custom components and app code
     *
     * @param $name
     * @param array $helpers
     * @param array $decorators
     * @param bool $report_exceptions
     */
    function __construct($name,  array $helpers = [], array $decorators = [], $report_exceptions = false) {

        $this->appName = $name;

        $this->helpers = $helpers;

        $this->decorators = $decorators;

        $this->repost_exceptions = $report_exceptions;

        self::register('boot', "var {$this} = {$this} || this, self =  self || {$this}, window = window || this;", [], true);
    }

    /**
     * Add an extension
     *
     * @param JSExtension $ext
     * @return $this
     */
    public function addExtension(JSExtension $ext)
    {
        $this->extensions[$ext->getName()] = $ext;

        return $this;
    }





    /**
     * Add a helper. This helper will be as a global object in v8 context available in variable $name
     *
     * @param $name
     * @param $helper
     * @return $this
     */
    public function addHelper($name, $helper)
    {
        $this->helpers[$name] = $helper;

        return $this;
    }

    /**
     *
     */
    public function boot()
    {

        $map = [];
        $this->buildExtensionsMap($this->extensions, $map);

        foreach($map as $ext){
            call_user_func_array([$this, 'register'], $ext);
        }




        $this->engine = new \V8Js($this->appName, [], [], $this->repost_exceptions);

        $app = array();

        foreach ($this->helpers as $helperName => $helper) {
            $this->engine->{$helperName} = $helper;
            $app[] = "var {$helperName} = window.{$helperName} = self.{$helperName} = {$this->appName}.{$helperName}; ";
        }
        
        foreach ($this->decorators as $name => $decorator)
        {
            $app[] = $decorator->getScriptContent();
            $app[] = "var {$name} = this.{$name} = {$this->appName}.%s;\n";
        }


        foreach ($this->scripts as $script){
            $app[] = file_get_contents($script);
        }

        $this->appCode = implode("\n", $app);

        //$this->executeScript($this->appCode, 'context.js');
    }

    /**
     * Compile un string an return a resource
     *
     *
     * @param $code
     * @param $jsFilename
     * @return resource
     * @throws \V8JsScriptException
     */
    public function compileString($code, $jsFilename)
    {
        if(!$this->engine instanceof \V8Js){
            $this->boot();
        }

        try {
            return  $this->engine->compileString($code, $jsFilename);

        } catch (\V8JsScriptException $e) {
            var_dump($e);
            throw $e;
        }
    }

    /**
     * @param $script
     * @return string
     * @throws \Exception
     */
    public function executeScript($script, $name = null)
    {

        if(!$this->engine instanceof \V8Js){
            $this->boot();
        }

        $res = $this->compileString($script, $name);

        ob_start();
        $this->engine->executeScript($res);
        //$this->engine->executeScript($this->compileString($this->appCode, 'app'));
        print_r(ob_get_contents())
;        $result = ob_get_contents();
        ob_get_clean();

        return $result;
    }


    /**
     * Pipe the symfony logger to be used as the console object into V8 engine to log messages using monolog
     *
     * @return string
     */
    public function pipeLogger()
    {
        $this->engine->console = $this->logger;

        $this->engine->dump = function($msg){ return  function_exists('dump') ? dump($msg) : json_encode($msg); };

        $js =  <<< EOT
            var console = { 
                log: function(msg){ 
                    if(typeof msg === 'string')
                        {$this->appName}.console.info(msg)
                    else if(typeof msg === 'object')
                        {$this->appName}.dump(msg)
                    else
                        {$this->appName}.console.info('', msg)
                }, 
                warn: function(msg){ 
                    if(typeof msg === 'string')
                        {$this->appName}.console.warn(msg)
                    else if(typeof msg === 'object')
                        {$this->appName}.dump(msg)
                    else
                        {$this->appName}.console.warn('', msg)
                }, 
                error: function(msg){ 
                    if(typeof msg === 'string')
                        {$this->appName}.console.error(msg)
                    else if(typeof msg === 'object')
                        {$this->appName}.dump(msg)
                    else
                        {$this->appName}.console.error('Error', msg)
                }, 
                exception: function(msg){ 
                    if(typeof msg === 'string')
                        {$this->appName}.console.error(msg)
                    else if(typeof msg === 'object')
                        {$this->appName}.dump(msg)
                    else
                        {$this->appName}.console.error('Error', msg)
                } 
            };   
            
EOT;
        return $js;
    }


    /**
     * Executes Javascript using V8JS, with primitive exception handling
     *
     * @param string $js JS code to be executed
     * @return string The execution response
     * @throws \Exception
     * @throws \V8JsScriptException
     */
    public function execute($js, $debug = false) {

        try {
            ob_start();
            $this->engine->executeString($this->appCode. $js ?: '');
            $result = ob_get_contents();
            ob_get_clean();

            return $result;
        }
        catch (\V8JsScriptException $e) {

            throw  $e;
        }
        catch(\Exception $e){
            throw $e;
        }
    }

    /**
     * @param array $extensions
     * @return array
     */
    protected function buildExtensionsMap(array $extensions, &$map)
    {
       /**
         * @var JSExtension $ext
         */
        foreach($extensions as &$ext)
        {
            if(is_string($ext)){
                $ext = $this->getExtension($ext);
            }

            $this->buildExtensionsMap($ext->getDependencies(), $map);

            $map[$ext->getName()] = $ext->toCompatibleArray();
        }

        return $map;
    }



    /**
     * Add a script to the execution when booting the engine
     *
     *
     * @param $path
     * @return $this
     */
    public function addScript($path)
    {
        $this->scripts[] = $path;

        return $this;
    }

    /**
     * @param V8Extension $decorator
     * @return $this
     */
    public function addDecorator(V8Extension $decorator)
    {
        $this->decorators[$decorator->getName()] = $decorator;

        dump($this->decorators);

        return $this;
    }

    /**
     * Sets a logger instance on the object
     *
     * @param LoggerInterface $logger
     * @return null
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @return \V8Js
     */
    public function getEngine()
    {
        return $this->engine;
    }

    /**
     * @param \V8Js $engine
     * @return $this
     */
    public function setEngine($engine)
    {
        $this->engine = $engine;
        return $this;
    }

    /**
     * @return string
     */
    public function getAppName()
    {
        return $this->appName;
    }

    /**
     * @param string $appName
     * @return $this
     */
    public function setAppName($appName)
    {
        $this->appName = $appName;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAppCode()
    {
        return $this->appCode;
    }

    /**
     * @param mixed $appCode
     * @return $this
     */
    public function setAppCode($appCode)
    {
        $this->appCode = $appCode;
        return $this;
    }

    /**
     * @return array
     */
    public function getHelpers()
    {
        return $this->helpers;
    }

    /**
     * @param array $helpers
     * @return $this
     */
    public function setHelpers($helpers)
    {
        $this->helpers = $helpers;
        return $this;
    }

    /**
     * @return array
     */
    public function getDecorators()
    {
        return $this->decorators;
    }

    /**
     * @param array $decorators
     * @return $this
     */
    public function setDecorators($decorators)
    {
        $this->decorators = $decorators;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getScripts()
    {
        return $this->scripts;
    }

    /**
     * @param mixed $scripts
     * @return JsApp
     */
    public function setScripts($scripts)
    {
        $this->scripts = $scripts;
        return $this;
    }


    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->getAppName();
    }

    /**
     * @param $name
     * @return JSExtension
     * @throws \Exception
     */
    public function getExtension($name)
    {

        if(!array_key_exists($name, $this->extensions))
           throw new \Exception("Extension '{$name}' is not registered within {$this} application");

        return $this->extensions[$name];

    }

    /**
     * @return Extensions\JSExtension[]
     */
    public function getExtensions()
    {
        return $this->extensions;
    }

    /**
     * @param Extensions\JSExtension[] $extensions
     * @return JSApplication
     */
    public function setExtensions($extensions)
    {
        $this->extensions = $extensions;
        return $this;
    }



}