<?php
/*
NOTICE OF LICENSE

This source file is subject to the SafeMageEULA that is bundled with this package in the file LICENSE.txt.

It is also available at this URL: http://www.safemage.com/LICENSE_EULA.txt

Copyright (c)  SafeMage (http://www.safemage.com/)
*/


class SafeMage_Plugin_Renderer extends Varien_Object
{
    protected $_phpCode;

    /**
     * SafeMage_Plugin_Renderer constructor.
     *
     */
    public function __construct()
    {
        parent::__construct();
        define('NL', PHP_EOL . '    ');
        define('NL2', PHP_EOL . '        ');
    }

    /**
     * @param $className string
     */
    public function init($className)
    {
        $this->_phpCode = '';
        $this->setClassName($className);
    }

    /**
     * @param $method string
     * @param $pluginsData array
     */
    public function addPluginsForMethod($method, $pluginsData)
    {
        $before = array();
        if (isset($pluginsData[SafeMage_Plugin::TYPE_BEFORE])) {
            $beforePlugins = $pluginsData[SafeMage_Plugin::TYPE_BEFORE];
            foreach($beforePlugins as $plugin) {
                $runParts = explode('::', (string)$plugin->run, 2);
                $pluginClass  = $runParts[0];
                $pluginMethod = $runParts[1];
                $before[] = 'Mage::getSingleton(\'' . $pluginClass .'\')->'. $pluginMethod . '($this, $arguments);';
            }
        }

        $around = array();
        if (isset($pluginsData[SafeMage_Plugin::TYPE_AROUND])) {
            $aroundPlugins = $pluginsData[SafeMage_Plugin::TYPE_AROUND];
            foreach($aroundPlugins as $plugin) {
                $runParts = explode('::', (string)$plugin->run, 2);
                $pluginClass  = $runParts[0];
                $pluginMethod = $runParts[1];
                $around[] = '$result = Mage::getSingleton(\'' . $pluginClass .'\')->'. $pluginMethod . '($this, $arguments);';
            }
        }

        $after = array();
        if (isset($pluginsData[SafeMage_Plugin::TYPE_AFTER])) {
            $afterPlugins = $pluginsData[SafeMage_Plugin::TYPE_AFTER];
            foreach($afterPlugins as $plugin) {
                $runParts = explode('::', (string)$plugin->run, 2);
                $pluginClass  = $runParts[0];
                $pluginMethod = $runParts[1];
                $after[] = '$result = Mage::getSingleton(\'' . $pluginClass .'\')->'. $pluginMethod . '($this, $result, $arguments);';
            }
        }

        $this->_phpCode .= $this->_renderMethod($method, $before, $around, $after);
    }

    /**
     * @param $pluginClassName string
     */
    public function createClass($pluginClassName)
    {
        Mage::getSingleton('SafeMage_Plugin_File')->create($pluginClassName, $this->_renderClass($pluginClassName));
    }

    /**
     * @param $pluginClassName string
     * @return string
     */
    protected function _renderClass($pluginClassName)
    {
        $className = $this->getClassName();

        $code = '<?php ' . PHP_EOL;
        $code .= 'class ' . $pluginClassName . ' extends ' . $className;
        $code .= PHP_EOL . '{';
        $code .= $this->_phpCode;
        $code .= PHP_EOL . '}';

        return $code;
    }

    /**
     * @param $method string
     * @param $before array
     * @param $around array
     * @param $after array
     * @return string
     * @throws Exception
     */
    protected function _renderMethod($method, $before, $around, $after)
    {
        $className = $this->getClassName();
        $oReflectionClass = new ReflectionClass($className);
        $method = $oReflectionClass->getMethod($method);

        $this->_validateMethod($method);

        $sParamsWithDefaults = array();
        $sParams = array();

        foreach($method->getParameters() as $param) {
            $sParams[]= '$' . $param->name;
            $sParamWithDefaults = '$' . $param->name;
            if ($param->isDefaultValueAvailable()) {
                $sParamWithDefaults .= ' = ' . $this->_varExport($param->getDefaultValue());
            }

            if ($param->isPassedByReference()) {
                $sParamWithDefaults = '&' . $sParamWithDefaults;
            }

            if ($param->isArray()) {
                $sParamWithDefaults = 'array ' . $sParamWithDefaults;
            } elseif($paramClass = $param->getClass()) {
                $sParamWithDefaults = $paramClass->name . ' ' . $sParamWithDefaults;
            }

            $sParamsWithDefaults[]= $sParamWithDefaults;
        }

        $sParamsWithDefaults = implode(', ', $sParamsWithDefaults);
        $sParams = implode(', ', $sParams);

        $sModifiers = implode(' ', Reflection::getModifierNames($method->getModifiers()));
        $code = NL . $sModifiers . ' function ' . $method->name . '(' . $sParamsWithDefaults . ')';
        $code .= NL . '{';

        $code .= NL2 . '$arguments = array(' . $sParams . ');' . NL2;

        if ($before) {
            $code .= NL2 . '// BEFORE';
            $code .= NL2 . implode(NL2, $before) . NL2;
        }

        $code .= NL2;
        $code .= '// CURRENT METHOD' . NL2;
        $callParent = '$result = parent::' . $method->name . '(' . $sParams . ');';
        if ($around) {
            $callParent = '//' . $callParent . NL2;
            $callParent .= NL2 . '// AROUND';
            $callParent .= NL2 . implode(NL2, $around);
        } elseif($sParams && $before) {
            $code .=  'list(' . $sParams . ') = $arguments;' . NL2;
        }
        $code .= $callParent . NL2;

        if ($after) {
            $code .= NL2 . '// AFTER';
            $code .= NL2 . implode(NL2, $after) . NL2;
        }

        $code .= NL2 . 'return $result;';
        $code .= NL . '}';

        return $code;
    }

    protected function _varExport($var)
    {
        $var = var_export($var, 1);
        $var = str_replace(array("\r", "\n", ' '), array('', '', ''), $var);
        return $var;
    }

    /**
     * @param ReflectionMethod $method
     * @throws Exception
     */
    protected function _validateMethod(ReflectionMethod $method)
    {
        if ($method->isFinal()) {
            throw new Exception("Cannot override final method");
        }

        if ($method->isPrivate()) {
            throw new Exception("Cannot override private method");
        }
    }
}
