<?php
/**
 * Triad - Lightweight MVP / HMVP Framework
 * @link http://
 * @author Marek Vavrecan, vavrecan@gmail.com
 * @copyright 2013 Marek Vavrecan
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3
 * @version 1.0.0
 */

namespace Triad\Requests;

use \Triad\Exceptions;
use \Triad\Request;
use \Triad\RequestMethod;
use \Triad\Utils;

use \Triad\Response;
use \Triad\Responses;

/**
 * Most important constants
 * Class HttpStatusCode
 * @package Triad\Requests
 */
final class HttpStatusCode
{
    const OK = 200;
    const MOVED_PERMANENTLY = 301;
    const MOVED_TEMPORARILY = 302;
    const BAD_REQUEST = 400;
    const UNAUTHORIZED = 401;
    const FORBIDDEN = 403;
    const NOT_FOUND = 404;
    const INTERNAL_SERVER_ERROR = 500;
}

final class HttpRequestMethod
{
    const PUT = 'PUT';
    const GET = 'GET';
    const POST = 'POST';
    const DELETE = 'DELETE';
}

final class HttpRequestProtocol
{
    const HTTP = 'http';
    const HTTPS = 'https';
}

/**
 * This class extends framework Request and allows it to be used with http protocol
 * Class HttpRequest
 * @package Triad\Requests
 */
class HttpRequest extends Request
{
    protected $host = null;
    protected $protocol = HttpRequestProtocol::HTTP;

    public $files = array();
    private $supportedHttpProtocols = array(HttpRequestProtocol::HTTP, HttpRequestProtocol::HTTPS);
    protected $internalRequest = false;

    public static function factory($path, $params = array(), $response = null) {
        $request = new HttpRequest($path, $params, $response);
        return $request;
    }

    public final function setFiles($files) {
        $this->files = $files;
    }

    public final function getFiles() {
        return $this->files;
    }

    public final function setProtocol($protocol) {
        if (!in_array(strtolower($protocol), $this->supportedHttpProtocols))
            throw new Exceptions\TriadException("Unsupported protocol");

        $this->protocol = strtolower($protocol);
    }

    public final function getProtocol() {
        return $this->protocol;
    }

    public final function secure() {
        return $this->protocol === HttpRequestProtocol::HTTPS;
    }

    public function getPathArgument($idx) {
        static $pathArguments;

        if ($pathArguments == null)
            $pathArguments = explode("/", trim($this->path, "/"));

        if (array_key_exists($idx, $pathArguments))
            return $pathArguments[$idx];

        return null;
    }

    public final function setHost($host) {
        $this->host = $host;
    }

    public final function getHost() {
        return $this->host;
    }

    public final function setMethodHttp($httpMethod) {
        switch (strtolower($httpMethod)) {
            case HttpRequestMethod::DELETE:
                $this->setMethod(RequestMethod::DELETE);
                break;
            case HttpRequestMethod::GET:
                $this->setMethod(RequestMethod::READ);
                break;
            case HttpRequestMethod::POST:
                $this->setMethod(RequestMethod::CREATE);
                break;
            case HttpRequestMethod::PUT:
                $this->setMethod(RequestMethod::UPDATE);
                break;
            default:
                // this is not really needed, exception handler is loader later
                // throw new \InvalidArgumentException("Unsupported http request method");
        }
    }

    /**
     * Trim base from request
     * @param $base
     * @throws \InvalidArgumentException
     */
    public function setBasePath($base) {
        $base = rtrim($base, "/");
        if (substr($this->path, 0, strlen($base)) !== $base)
            throw new \InvalidArgumentException("Current path is different than base path " . $this->path);

        $this->path = substr($this->path, strlen($base));
    }

    /**
     * Creates a new request from server request
     * @param null $response Default response type, for example \Triad\Responses\JsonResponse
     * @return HttpRequest
     * @throws \InvalidArgumentException
     */
    public static function fromServerRequest($response = null) {
        $params = array();
        $path = "/";

        $requestUri = isset($_SERVER["REQUEST_URI"]) ? $_SERVER["REQUEST_URI"] : "";
        Utils::parseUrlPath($requestUri, $path, $params);

        // construct new http request object
        $request = new HttpRequest($path, $params, $response);

        // if request is done over xml http request, set response format to json
        if (!isset($params["response_format"]) && isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
            $params["response_format"] = Responses\JsonResponse::NAME;

        // handle response type
        if (isset($params["response_format"])) {
            switch($params["response_format"]) {
                case Responses\JsonResponse::NAME:
                    $response = new Responses\JsonResponse();
                    break;
                case Responses\PhpSerializeResponse::NAME:
                    $response = new Responses\PhpSerializeResponse();
                    break;
                default:
                    $response = new Response();
            }
            $request->setResponse($response);
        }

        // handle some more json options
        if ($request->getResponse() instanceof \Triad\Responses\JsonResponse) {
            if (isset($params["pretty"]))
                $request->getResponse()->setPretty($params["pretty"]);

            if (isset($params["callback"]))
                $request->getResponse()->setCallback($params["callback"]);
        }

        // set host
        if (isset($_SERVER["HTTP_HOST"])) {
            $request->setHost($_SERVER["HTTP_HOST"]);
        }

        // set method
        if (isset($_SERVER["REQUEST_METHOD"])) {
            $request->setMethodHttp($_SERVER["REQUEST_METHOD"]);
        }

        // set protocol
        if (isset($_SERVER["HTTPS"]) && !empty($_SERVER["HTTPS"])) {
            $request->setProtocol(HttpRequestProtocol::HTTPS);
        }

        // handle method override
        if (isset($request->params["method"])) {
            $request->setMethodHttp($request->params["method"]);

            // hide the argument
            unset($request->params["method"]);
        }

        // update arguments by post params (priority over get)
        if (isset($_POST) && is_array($_POST) && count($_POST) > 0) {
            $getParams = $request->params;
            // post can be consuming, just past as reference
            $request->params = &$_POST;
            $request->params += $getParams;
        }

        // uploaded files
        if (isset($_FILES) && is_array($_FILES) && count($_FILES) > 0) {
            $request->files = &$_FILES;
        }

        return $request;
    }
}
