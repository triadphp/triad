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
 * This class will autoload files if they have namespace
 * File path must match with namespace path
 *
 * eg. for class Namespace\Sub\Class we will try to include file Namespace/Sub/Class.php
 */
class Autoload
{
    protected static $registered = false;
    protected static $registeredNamespaces = array();

    public static function loadClass($class) {
        // extract namespace from class
        $extractedNamespace = Utils::extractNamespace($class);

        foreach (self::$registeredNamespaces as $namespace => $basePath) {
            // check if namespace matches
            if ($extractedNamespace == $namespace) {
                // extract namespace from beginning of the string
                $path = substr($class, strlen($namespace) + 1);

                // replace class separators with directory separators
                $path = str_replace("\\", defined("DIRECTORY_SEPARATOR") ? DIRECTORY_SEPARATOR : "/", $path);

                // try to include if exists
                $includeFile = $basePath . (defined("DIRECTORY_SEPARATOR") ? DIRECTORY_SEPARATOR : "/") . $path . ".php";

                if (file_exists($includeFile)) {
                    require_once($includeFile);
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Registers the Autoloader to Load Classes with the namespace.
     * @return bool
     */
    public static function register() {
        // only allow one initialization
        if (self::$registered) {
            return false;
        }

        // Register the Loader
        self::$registered = spl_autoload_register("\\" . __NAMESPACE__ . "\\Autoload::loadClass");
        return self::$registered;
    }

    /**
     * Namespace we can auto load classes
     * @param $namespace
     * @param $basePath
     */
    public static function add($namespace, $basePath) {
        self::$registeredNamespaces[$namespace] = $basePath;
    }
}
