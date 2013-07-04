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

use \Triad\Exceptions;

class Utils
{
    /**
     * Parse url
     * @param $request_uri
     * @param $path
     * @param $args
     * @return bool
     */
    public static function parseUrlPath($request_uri, &$path, &$args) {
        $expression = "/^(?P<path>[^\?]*)(\?(?P<args>.*))?$/u";

        if (preg_match($expression, $request_uri, $params)) {

            // get path component
            if (isset($params["path"])) {
                $path = urldecode($params["path"]);
            }

            // parse parameters
            if (isset($params["args"])) {
                parse_str($params["args"], $args);
            }

            return true;
        }

        return false;
    }

    public static function getObjectName($string) {
        if (preg_match("/^[a-z0-9\\_]+$/i", $string)) {
            $string = str_replace("_", " ", $string);
            $string = ucwords($string);
            $string = str_replace(" ", "", $string);
            return $string;
        }

        return null;
    }

    public static function extractClassName($class) {
        $class = explode("\\", $class);
        end($class);
        return current($class);
    }

    public static function extractNamespace($class) {
        $class = explode("\\", $class);
        return current($class);
    }

    public static function generateRandomKey($length) {
        // try to generate random key using openssl
        if (function_exists("openssl_random_pseudo_bytes")) {
            $bytes = openssl_random_pseudo_bytes($length);
        }
        else {
            mt_srand();

            $bytes = "";
            for ($i = 0; $i < $length; $i++)
                $bytes .= chr(mt_rand(0, 0xff));
        }

        $hex = bin2hex($bytes);
        if (strlen($hex) != $length * 2)
            throw new Exceptions\TriadException("The generation of random key failed");

        return $hex;
    }
}

class Validators
{
    public static function isEmail($email) {
        return preg_match("/^[^\\s]+@[^\\s]+\\.[^\\s]+$/s", $email);
    }

    public static function isUrl($url) {
        return preg_match("/^https?:\\/\\/[^\\S]+(\\/(.*?))?$/u", $url);
    }

    public static function assertEmail($email) {
        if (!self::isEmail($email))
            throw new Exceptions\ValidationException("Invalid Email: " . $email);
    }

    public static function assertUrl($url) {
        if (!self::isUrl($url))
            throw new Exceptions\ValidationException("Invalid Url: " . $email);
    }
}