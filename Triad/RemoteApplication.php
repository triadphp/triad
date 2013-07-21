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

use Triad\Exceptions\RemoteException;
use Triad\Exceptions\TriadException;
use Triad\Requests\HttpRequestMethod;

/**
 * Class RemoteApplication
 * Simple application that runs on external server
 *
 * Example call
 * Request::factory("/1", array("p1" => 0))->execute(\Triad\RemoteApplication::factory("server01"))->response->get();
 *
 * Example call
 * $remoteServer = \Triad\RemoteApplication::factory(array(
 *   "url" => "http://server02",
 *   "base_path" => "/triad/www/",
 *   "client_secret" => "shared_secret"
 * ));
 * Request::factory("/2")->execute($remoteServer)->response->get();
 *
 * @package Triad
 */
class RemoteApplication implements IApplication
{
    private $server = null;
    private $clientSecret = null;
    private $basePath = null;

    /**
     * Create a new remote application
     * @param $params param can be either string of array with settings (url, base_path, client_secret)
     * @throws \InvalidArgumentException
     */
    public function __construct($params) {
        if (is_string($params))
            $params = array("url" => $params);

        if (!isset($params["url"]))
            throw new \InvalidArgumentException("Missing url");

        if (isset($params["base_path"]))
            $this->basePath = rtrim($params["base_path"], "/");

        if (isset($params["client_secret"]))
            $this->clientSecret = $params["client_secret"];

        $this->verifyHostSyntax($params["url"]);
        $this->server = $params["url"];
    }

    /**
     * @param string|array $params
     * @return \Triad\RemoteApplication
     */
    public static function factory($params) {
        $application = new RemoteApplication($params);
        return $application;
    }

    public function execute(Request $request)
    {
        // lets ask for json response
        $request->params["response_format"] = \Triad\Responses\JsonResponse::NAME;

        // add client signature if client secret is available
        if (!is_null($this->clientSecret))
            $request->applySignature($this->clientSecret);

        $requestUri = $this->server . $this->basePath . $request->path;
        $httpMethod = $this->getMethodHttp($request->method);

        // build query from params
        $rawQuery = http_build_query($request->params, '', '&');

        // context for file stream
        $httpContext = array(
            'method' => strtoupper($httpMethod),
            'ignore_errors' => true,
            'follow_location' => true,
            'timeout' => 20
        );

        // handle post method
        if ($httpMethod == HttpRequestMethod::POST) {
            $httpContext["header"] = "Content-Type: application/x-www-form-urlencoded; charset=utf-8\r\n" .
                                     "Content-Length: " . strlen($rawQuery);
            $httpContext["content"] =  $rawQuery;
        }
        else {
            $requestUri .= "?{$rawQuery}";
        }

        $context = array(
            "http" => $httpContext
        );

        $rawResponse = file_get_contents($requestUri, false, stream_context_create($context));
        $response = json_decode($rawResponse, true);

        // json decoding must success
        if (json_last_error() > 0)
            throw new TriadException("Unable to parse response json: " . $rawResponse);

        // check if response header is 200 otherwise error with content
        if (!$this->isResponseOk($http_response_header))
            throw new RemoteException($response, $http_response_header);

        $request->response->set($response);
        return $this;
    }

    private function isResponseOk($httpResponse) {
        // check from end because of the possible redirects before
        for ($i = count($httpResponse) - 1; $i >= 0; $i--)
            if (preg_match("#^HTTP/.*200#", $httpResponse[$i]))
                return true;

        return false;
    }

    private function verifyHostSyntax($url) {
        $parsedComponents = parse_url($url);

        if (!isset($parsedComponents["scheme"]))
            throw new \InvalidArgumentException("Missing scheme in $url");

        if (!isset($parsedComponents["host"]))
            throw new \InvalidArgumentException("Missing host in $url");

        if (isset($parsedComponents["path"]))
            throw new \InvalidArgumentException("You should not pass path component in a remote application " .
                "declaration - use path in request object instead - in $url");
    }

    /**
     * Return http request word
     * @param $method
     * @return string HTTP request method
     * @throws \InvalidArgumentException
     */
    private function getMethodHttp($method) {
        switch (strtolower($method)) {
            case RequestMethod::DELETE:
                return HttpRequestMethod::DELETE;
                break;
            case RequestMethod::READ:
                return HttpRequestMethod::GET;
                break;
            case RequestMethod::CREATE:
                return HttpRequestMethod::POST;
                break;
            case RequestMethod::UPDATE:
                return HttpRequestMethod::PUT;
                break;
            default:
                throw new \InvalidArgumentException("Unsupported request method {$method}");
        }
    }

    public function handleException(\Exception $e, Request $request) {
        throw new \Triad\TriadException("Remote Application Exception handler should not be executed directly");
    }

    public function handleResponseException(\Exception $e, \Triad\Response $response) {
        // not really important for remote calls
    }
}