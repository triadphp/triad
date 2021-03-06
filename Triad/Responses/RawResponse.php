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

class RawResponse extends HttpResponse {
    private $data;

    public function __construct($data) {
        $this->data = $data;
    }

    public function before() {
        $this->addHeader("Content-type: text/plain; charset=UTF-8");
    }

    public function outputBody() {
        print($this->data);
    }
}