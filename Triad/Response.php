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

/**
 * Class Response
 * Response goes under request for easy access and custom responses handles specific render output (json, template, etc)
 * Response should load template engines lazy - in output body method
 * @package Triad
 */
class Response implements \ArrayAccess {
    protected $container = array();
    protected $exceptionHandler = null;

    public function __construct() {
    }

    public function setExceptionHandler($callback) {
        $this->exceptionHandler = $callback;
    }

    protected function outputBody() {
        print "<pre>";
        var_dump($this->container);
        print "</pre>";
    }

    protected function before() {
    }

    protected function after() {
    }

    public function send() {
        try {
            $this->before();
            $this->outputBody();
            $this->after();
        }
        catch (\Exception $e) {
            if (is_callable($this->exceptionHandler))
                call_user_func_array($this->exceptionHandler, array($e, $this));
        }
    }

    public function clear() {
        $this->container = array();
    }

    public function &get() {
        return $this->container;
    }

    public function set($value) {
        $this->container = &$value;
    }

    #[\ReturnTypeWillChange]
    public function offsetExists($offset) {
        return isset($this->container[$offset]);
    }

    #[\ReturnTypeWillChange]
    public function offsetGet($offset) {
        return $this->container[$offset];
    }

    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value) {
        $this->container[$offset] = $value;
    }

    #[\ReturnTypeWillChange]
    public function offsetUnset($offset) {
        unset($this->container[$offset]);
    }
}
