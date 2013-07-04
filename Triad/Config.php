<?php
/**
 * Triad - Lightweight MVP / HMVP Framework
 * @link http://
 * @author Marek Vavrecan, vavrecan@gmail.com
 * @copyright 2013 Marek Vavrecan
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3
 * @version 1.0.0
 */

namespace Triad;

use \Triad\Exceptions\TriadException;

/**
 * Class Config
 * Readonly class for config files
 * @package Triad
 */
class Config implements \ArrayAccess
{
    protected $container = array();

    public function load($config_file) {
        $config_data = include($config_file);
        $this->container = array_merge($this->container, $config_data);
    }

    public static function factory($config_file) {
        $config = new Config();
        $config->load($config_file);
        return $config;
    }

    public function getVal($key, $sub) {
        if (isset($this->container[$key][$sub])) {
            return $this->container[$key][$sub];
        }
        return null;
    }

    public function &__get($name) {
        if (isset($this->container[$name])) {
            return $this->container[$name];
        }
        return null;
    }

    function __set($name, $value) {
        throw new TriadException("Config is read only");
    }

    public function offsetExists($offset) {
        return isset($this->container[$offset]);
    }

    public function &offsetGet($offset) {
        return $this->container[$offset];
    }

    public function offsetSet($offset, $value) {
        throw new TriadException("Config is read only");
    }

    public function offsetUnset($offset) {
        throw new TriadException("Config is read only");
    }
}