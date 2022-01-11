<?php

declare(strict_types=1);

namespace Itseasy\Plugin;
use BadMethodCallException;

trait PluginAwareTrait
{
    // Note : Trait properties are not overridable

    // attached plugin list
    private $_plugins = [];

    // attached plugin alias
    private $_capabilities = [];

    // Override to define custom plugin list, default is empty
    // Plugin cannot individually attachable or remove
    public function getAttachedPlugin() : array
    {
        return self::$_plugins;
    }

    public function __call($function, $args)
    {
        // Only populate when needed and only once
        $this->_registerCapabilities();

        if (empty($this->_capabilities[$function])) {
            throw new BadMethodCallException($function." Not exist");
        }

        return call_user_func_array($this->_capabilities[$function], $args);
    }

    private function _registerCapabilities() : void
    {
        if (!count($this->_capabilities)) {
            foreach ($this->getAttachedPlugin() as $plugin) {
                if (is_string($plugin)) {
                    $plugin = new $plugin;
                }

                if (!$plugin instanceof PluginInterface) {
                    continue;
                }

                if (!is_callable($plugin)) {
                    continue;
                }

                $this->_capabilities[$plugin->getName()] = $plugin;
            }
        }
    }
}
