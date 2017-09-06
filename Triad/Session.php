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

class Session implements \ArrayAccess
{
    private $duration;
    private $prefix;

    private $secureOnly = false;
    private $domain = "";
    private $path = "/";

    public function __construct() {
        $this->duration = 60*60; // 1 hours
        $this->prefix = "";
    }

    public function setSecurity($domain, $path, $secureOnly) {
        $this->domain = $domain;
        $this->path = $path;
        $this->secureOnly = $secureOnly;
    }

    public function setDuration($duration) {
        $this->duration = $duration;
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
        $_COOKIE[$this->prefix . $offset] = $value;
        $this->setSecureCookie($this->prefix . $offset, $value, $this->duration);
    }

    public function offsetUnset($offset)
    {
        $this->setSecureCookie($this->prefix . $offset, "", -$this->duration);
        unset($_COOKIE[$this->prefix . $offset]);
    }

    protected function setSecureCookie($cookie, $value, $duration) {
        // build cookie manually to support SameSite policy
        $expires = \gmdate('D, d-M-Y H:i:s T', time() + $duration);
        $cookieString = "{$cookie}=" . urlencode($value) . "; expires={$expires}; Max-age=" . (int)$duration . "; path={$this->path}";

        if (!empty($this->domain)) {
            // remove port
            $domain = preg_replace("/(?P<port>:\d+)$/", "", $this->domain);
            $cookieString .= "; domain={$domain}";
        }

        if ($this->secureOnly)
            $cookieString .= "; secure";

        $cookieString .= "; HttpOnly";

        if ($this->secureOnly)
            $cookieString .= "; SameSite=Strict";

        if (!headers_sent())
            header("Set-Cookie: {$cookieString}");

        // setcookie($cookie, $value, time() + $duration, $this->path, $this->domain, $this->secureOnly, true);
    }
}