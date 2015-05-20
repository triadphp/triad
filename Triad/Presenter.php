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

use Triad\Request;

abstract class Presenter
{
    /**
     * @var Request
     */
    protected $request;
    /**
     * @var array
     */
    protected $return;
    /**
     * @var Application
     */
    protected $application;
    /**
     * @var string
     */
    protected $actionName;
    /**
     * @var array
     */
    protected $params;

    /**
     * Ignore action in request and always route to default action [/presenter][/exteneded_path]
     * @var bool
     */
    protected $singleAction = false;

    /**
     * Create a new presenter
     * @param Application $application
     * @param Request $request
     * @param array $params
     */
    public function __construct(Application $application, Request $request, $params) {
        $this->request = $request;
        $this->application = $application;
        $this->params = $params;
        $this->return = null;
        $this->actionName = Utils::getObjectName($params["action"]);

        if ($this->singleAction) {
            $this->actionName = 'default';
            /* update extended path to contain action name in it */
            $this->params["extended_path"] = "/" . $params["action"] . $this->params["extended_path"];
        }
    }

    /**
     * Require request method to be passed in call
     * @param $method
     * @throws \ErrorException
     */
    protected function requireMethod($method) {
        if ($this->request->getMethod() != $method)
            throw new \ErrorException("Another request method is required: " . (string)$method);
    }


    /**
     * Require params to be passed in call
     * @param array $params
     * @throws \ErrorException
     */
    protected function requireParams($params) {
        if (!is_array($params))
            $params = array((string)$params);

        foreach ($params as $param) {
            if (!isset($this->request->params[$param]) || strlen($this->request->params[$param]) == 0)
                throw new \ErrorException("Additional parameter is missing: " . (string)$param);
        }
    }

    public function before() {
    }

    public function after() {
    }

    /**
     * Handle presenter action, run before and after methods
     * @throws Exceptions\NotFoundException Thrown if there is missing action handler in presenter
     */
    public function execute() {
        // determine action function
        $action = 'action' . $this->actionName;

        // check if action handler exists
        if (!method_exists($this, $action)) {
            throw new Exceptions\NotFoundException("Unknown action: " . $this->actionName);
        }

        // handle request
        $this->before();

        // execute action of the presenter
        $return = $this->{$action}();

        // response data were returned from presenter, overwrite them
        if (!is_null($return))
            $this->return = $return;

        $this->after();

        // set response data
        $this->request->response->set($this->return);
    }

    /**
     * Redirect to another url using RedirectResponse
     * @param string $url
     * @param int $httpResponseCode
     */
    public function redirect($url, $httpResponseCode = \Triad\Requests\HttpStatusCode::MOVED_TEMPORARILY) {
        $redirect = new \Triad\Responses\RedirectResponse($url, $httpResponseCode);
        $this->request->setResponse($redirect);
    }
}
