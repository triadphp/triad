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

class RedirectResponse extends HttpResponse {
    private $url;
    private $httpResponseCode;

    public function __construct($url, $httpResponseCode = 301) {
        $this->url = $url;
        $this->httpResponseCode = $httpResponseCode;
    }

    public function before() {
        $this->emptyHeaders();

        $this->setResponseCode($this->httpResponseCode);
        $this->addHeader("Location: " . $this->url);
    }

    /**
     * Overwrite parent output body so we always get empty page
     */
    public function outputBody() {
    }
}