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

class NotFoundException extends TriadException
{
    public function isFatal() {
        return false;
    }

    public function getHttpCode() {
        return \Triad\Requests\HttpStatusCode::NOT_FOUND;
    }
}
