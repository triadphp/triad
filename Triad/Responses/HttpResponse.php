<?php
/**
 * Triad - Lightweight MVP / HMVP Framework
 * @link http://
 * @author Marek Vavrecan, vavrecan@gmail.com
 * @copyright 2013 Marek Vavrecan
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3
 * @version 1.0.0
 */

namespace Triad\Responses;

use Triad\Response;

class HttpResponse extends Response {
    private $headers = array();
    private $responseCode = null;

    public final function emptyHeaders() {
        $this->headers = array();
    }

    public final function addHeader($header) {
        $this->headers[] = $header;
    }

    public final function &getHeaders() {
        return $this->headers;
    }

    public final function setResponseCode($httpResponseCode) {
        $this->responseCode = $httpResponseCode;
    }

    public final function outputHeaders() {
        if ($this->responseCode != null) {
            header("HTTP/1.1 " . $this->responseCode, true, $this->responseCode);
        }

        if (!headers_sent()) {
            foreach ($this->headers as $header) {
                header($header);
            }
        }
    }

    public function send() {
        $this->before();
        $this->outputHeaders();
        $this->outputBody();
        $this->after();
    }
}