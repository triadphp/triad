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

final class RouteType
{
    const MVP = 'mvp';
    const SIMPLE = 'simple';
    const CALLBACK = 'callback';
}

/**
 * Class Router
 * Matches route from request using suppported patterns (MVC, handle)
 * Items added first will have bigger priority
 * @package Triad
 */
class Router
{
    // list of route to execute
    private $routes;

    // check if router supports MVP handling
    private $mvpHandled;

    public function __construct() {
        $this->routes = array();
        $this->mvpHandled = false;
    }

    /**
     * Add custom simple route matcher
     * @param $type
     * @param $params
     * @throws \Triad\Exceptions\TriadException
     */
    private function push($type, $params) {
        // support only single MVP router per router
        if ($type ==  RouteType::MVP) {
            if ($this->mvpHandled)
                throw new \Triad\Exceptions\TriadException("MVP handler was already added to router");

            $this->mvpHandled = true;
        }
        else {
            if ($this->mvpHandled)
                throw new \Triad\Exceptions\TriadException("Custom router handlers must be added before MVP");
        }

        $params["type"] = $type;
        $this->routes[] = $params;
    }

    /**
     * Match simple
     * @param $path
     * @param callable $callback containing parameters application, request, params
     * @param bool $regex
     */
    public function add($path, $callback, $regex = false) {
        $this->push(
            RouteType::SIMPLE,
            array(
                "path" => $path,
                "regex" => $regex,
                "callback" => $callback
            )
        );
    }

    /**
     * Match by callback function
     * @param callable $callbackMatch containing parameters request
     * @param callable $callback containing parameters application, request, params
     */
    public function addCallback($callbackMatch, $callback) {
        $this->push(
            RouteType::CALLBACK,
            array(
                "callback_match" => $callbackMatch,
                "callback" => $callback
            )
        );
    }

    /**
     * Add MVP (Model-View-Presenter) route that consists of /presenter-id/action
     * @param string $namespace Namespace that handles MVP requests, this namespace must contain \{$namespace}\Presenters path
     */
    public function addMVP($namespace) {
        $this->push(
            RouteType::MVP,
            array(
                "namespace" => $namespace,
                "presenter" => "home", //default presenter name
                "action" => "default", // default presenter action
                "extended_path" => "",
                "id" => null // default parameter
            )
        );
    }

    /**
     * Match given path and set parameters
     * @param Request $request
     * @param array $params
     * @return bool|string false on non match, otherwise route type
     */
    public function match(Request $request, &$params) {
        $path = $request->getPath();

        foreach ($this->routes as $route) {
            $routeType = $route["type"];

            if ($routeType == RouteType::SIMPLE && !$route["regex"] && $path === $route["path"]) {
                $params = $route;
                return RouteType::SIMPLE;
            }

            if ($routeType == RouteType::SIMPLE && $route["regex"] && preg_match($route["path"], $path, $matches)) {
                $params = $route + $matches;
                return RouteType::SIMPLE;
            }

            if ($routeType == RouteType::CALLBACK) {
                $match = call_user_func_array($route["callback_match"], array($request));
                if ($match) {
                    $params = $route;
                    return RouteType::CALLBACK;
                }
            }

            // this match always leads to MVP, no matter what
            if ($routeType == RouteType::MVP) {
                // match [/presenter][/action][/extended_path]
                if (preg_match("#^/" .
                    "(?P<presenter>[^/]+)?" .
                    "/?(?P<action>[^/]+)?" .
                    "(?P<extended_path>(/([^/]+))*)" .
                    "$#", $path, $matches)) {
                    $params = $route;

                    foreach ($params as $param => &$value) {
                        // value exists
                        if (isset($matches[$param]) && strlen($matches[$param]) > 0)
                            $value = $matches[$param];
                    }

                    // check for presenter[-id]
                    if (preg_match("#^(?<presenter>.*?)-(?<id>\\d+)$#", $params["presenter"], $matches)) {
                        $params["presenter"] = $matches["presenter"];
                        $params["id"] = $matches["id"];
                    }

                    return RouteType::MVP;
                }
            }
        }

        return false;
    }
}