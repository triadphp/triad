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
 * This session is dummy
 * It just handles cookies
 */
class Session implements \ArrayAccess
{
    private $expiration;
    private $prefix;

    public function __construct() {
        $this->expiration = 60*60*24; // 24 hours
        $this->prefix = "";
    }

    public function offsetExists($offset)
    {
        return isset($_COOKIE[$this->prefix . $offset]);
    }

    public function offsetGet($offset)
    {
        return $_COOKIE[$this->prefix . $offset];
    }

    public function offsetSet($offset, $value)
    {
        setcookie($this->prefix . $offset, $value, time() + $this->expiration, "/", null, false, true);
        $_COOKIE[$this->prefix . $offset] = $value;
    }

    public function offsetUnset($offset)
    {
        setcookie($this->prefix . $offset, null, 1, "/", null, false, true);
        unset($_COOKIE[$this->prefix . $offset]);
    }
}