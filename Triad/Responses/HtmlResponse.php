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

class HtmlResponse extends HttpResponse {
    private $templateDir = null;
    private $template = null;
    private $templateRawBuffer = null;
    private $extension = ".php";

    public function __construct($templateDir = ".") {
        $this->templateDir = $templateDir;
        $this->template = "index";
    }

    public function setTemplate($template) {
        $this->template = $template;
    }

    public function setRaw($data) {
        $this->template = null;
        $this->templateRawBuffer = $data;
    }

    public function before() {
        $this->addHeader("X-Frame-Options: DENY");
        $this->addHeader("Content-type: text/html; charset=UTF-8");
    }

    public function outputBody() {
        // output directly from buffer
        if ($this->template == null) {
            echo $this->templateRawBuffer;
            return;
        }

        // set global variables
        if (is_array($this->container)) {
            foreach ($this->container as $key => $item)
                ${$key} = $item;
        }

        include($this->templateDir . "/" . $this->template . $this->extension);
    }
}
