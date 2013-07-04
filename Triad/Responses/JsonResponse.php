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

class JsonResponse extends HttpResponse {
    const NAME = "json";

    private $pretty;
    private $callback;

    public function __construct($pretty = true, $callback = null) {
        $this->pretty = $pretty;
        $this->callback = $callback;
    }

    public function setCallback($callback) {
        $this->callback = $callback;
    }

    public function getCallback() {
        return $this->callback;
    }

    public function setPretty($pretty) {
        $this->pretty = $pretty;
    }

    public function getPretty() {
        return $this->pretty;
    }

    public function before() {
        // allow all origins for json handling
        $this->addHeader("Access-Control-Allow-Origin: *");
        $this->addHeader("Content-type: text/javascript; charset=UTF-8");
    }

    public function outputBody() {
        // jsonp handling
        if (!is_null($this->callback))
            print "/**/ " . preg_replace("/[^A-Za-z0-9_\.\$]/u", "", $this->callback) . "(";

        $jsonOptions = JSON_HEX_TAG;

        // these are supported since php 5.4
        if (defined("JSON_UNESCAPED_SLASHES") && defined("JSON_UNESCAPED_UNICODE"))
            $jsonOptions |= JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;

        // disable pretty print if needed to save traffic
        if ($this->pretty && defined("JSON_PRETTY_PRINT"))
            $jsonOptions |= JSON_PRETTY_PRINT;

        // json body
        print json_encode($this->container, $jsonOptions);

        // end of jsonp handler
        if (!is_null($this->callback))
            print ");";
    }
}