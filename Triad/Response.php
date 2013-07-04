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

    public function __construct() {
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
        $this->before();
        $this->outputBody();
        $this->after();
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

    public function offsetExists($offset) {
        return isset($this->container[$offset]);
    }

    public function &offsetGet($offset) {
        return $this->container[$offset];
    }

    public function offsetSet($offset, $value) {
        $this->container[$offset] = $value;
    }

    public function offsetUnset($offset) {
        unset($this->container[$offset]);
    }
}
