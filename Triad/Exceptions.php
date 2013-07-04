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

class TriadException extends \Exception
{
    /**
     * Is exception fatal - these exceptions will be logged down
     * @return bool
     */
    public function isFatal() {
        return true;
    }

    public function getHttpCode() {
        return \Triad\Requests\HttpStatusCode::INTERNAL_SERVER_ERROR;
    }
}
