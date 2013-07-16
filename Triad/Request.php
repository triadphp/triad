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
use \Triad\Response;
use \Triad\Application;

final class RequestMethod
{
    const CREATE = 'create';
    const READ = 'read';
    const UPDATE = 'update';
    const DELETE = 'delete';
}

/**
 * Class Request
 * Triad Framework Request declaration
 *
 * Simple request example
 * \Triad\Request::factory("/request_path", array("params" => 1))->execute($application)->response->get()
 * @package Triad
 */
class Request implements \Serializable
{
    public $path = "/";
    public $method = RequestMethod::READ;
    public $params = array();

    private $parentRequest = null;
    private $nestingLevel = 0;

    /**
     * Is request being done client or server side?
     * @var bool
     */
    protected $internalRequest = true;

    /**
     * @var Response response
     */
    public $response = null;
    protected $responseHandler = null;

    /**
     * @param string $path
     * @param array $params
     * @param \Triad\Response $response default response type - can be overriden during application execution
     */
    public function __construct($path, $params = array(), $response = null) {
        $this->path = $path;
        $this->params = $params;

        // init with passed response or callback to primitive
        $this->setResponse($response == null ? new Response() : $response);
    }

    /**
     * Validates client hash in parameter with saved one
     * @param $clientSecret
     * @throws \Exception
     * @return $this
     */
    public function validateRequest($clientSecret) {
        // internal requests do not need verifications
        if ($this->isInternalRequest())
            return $this;

        if (!isset($this->params["request_signature"]))
            throw new \Exception("Request signature is required in order to call methods");

        $passedHash = $this->params["request_signature"];
        $validHash = $this->calculateSignature($clientSecret);

        if ($validHash !== $passedHash)
            throw new \Exception("Invalid request signature");

        return $this;
    }

    /**
     * Create request signature
     * @param $clientSecret
     * @return $this
     */
    public function applySignature($clientSecret) {
        $validHash = $this->calculateSignature($clientSecret);
        $this->params["request_signature"] = $validHash;
        return $this;
    }

    private function calculateSignature($clientSecret) {
        $params = $this->getParams();

        // do not hash request signature if present
        if (isset($params["request_signature"]))
            unset($params["request_signature"]);

        $validHash = hash("sha256", $clientSecret . $this->getPath() . http_build_query($params, '', '&'));
        return $validHash;
    }

    /**
     * @param string $path
     * @param array $params
     * @param \Triad\Response $response default response type
     * @return \Triad\Request
     */
    public static function factory($path, $params = array(), $response = null) {
        $request = new Request($path, $params, $response);
        return $request;
    }

    private $supportedMethods = array(
        RequestMethod::CREATE,
        RequestMethod::READ,
        RequestMethod::DELETE,
        RequestMethod::UPDATE
    );

    public function setNestingLevel($nestingLevel) {
        $this->nestingLevel = $nestingLevel;
    }

    public function &getNestingLevel() {
        return $this->nestingLevel;
    }

    public final function setParentRequest(Request $parentRequest) {
        // request which called this request
        $this->parentRequest = $parentRequest;

        // count nesting
        $this->nestingLevel = $parentRequest->getNestingLevel() + 1;
        return $this;
    }

    public final function getParentRequest() {
        return $this->parentRequest;
    }

    public final function setParams($params) {
        $this->params = $params;
        return $this;
    }

    public final function &getParams() {
        return $this->params;
    }

    public final function setMethod($method) {
        if (!in_array(strtolower($method), $this->supportedMethods))
            throw new Exceptions\TriadException("Unsupported method");

        $this->method = strtolower($method);
        return $this;
    }

    public final function &getMethod() {
        return $this->method;
    }

    public final function setPath($path) {
        $this->path = $path;
        return $this;
    }

    public final function &getPath() {
        return $this->path;
    }

    public function isInternalRequest() {
        return $this->internalRequest;
    }

    public final function setResponse($response) {
        // create class from string
        if (is_string($response) && class_exists($response, true)) {
            $response = new $response();
        }

        // require valid instance
        if ($response instanceof \Triad\Response) {
            $this->response = $response;
        } else {
            throw new \InvalidArgumentException("Invalid response instance");
        }

        return $this;
    }

    /**
     * @return Response|null
     */
    public final function &getResponse() {
        return $this->response;
    }

    /**
     * @param IApplication $application
     * @return Request
     */
    public final function execute(IApplication $application) {
        $application->execute($this);
        $this->response->setExceptionHandler(array($application, "handleResponseException"));
        return $this;
    }

    public function serialize()
    {
        $data = array(
            "path" => $this->path,
            "method" => $this->method,
            "params" => $this->params
        );

        return json_encode($data);
    }

    public function unserialize($serialized)
    {
        $data = json_decode($serialized, true);

        $this->path = $data["path"];
        $this->method = $data["method"];
        $this->params = $data["params"];
    }
}
