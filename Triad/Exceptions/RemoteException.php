<?php
/**
 * Triad - Lightweight MVP / HMVP Framework
 * @link http://
 * @author Marek Vavrecan, vavrecan@gmail.com
 * @copyright 2013 Marek Vavrecan
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3
 * @version 1.0.0
 */

namespace Triad\Exceptions;

class RemoteException extends TriadException
{
    public function __construct($response, $responseHeader) {
        $message = isset($response["error"]["message"]) ? $response["error"]["message"] : json_encode($response);
        parent::__construct($message);
    }
}