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

class DatabaseException extends TriadException
{
    public function __construct($pdoErrorInfo) {
        $message = "";

        // pdo error info at possition 2 contains some usefull message
        // http://php.net/manual/en/pdo.errorinfo.php
        if (isset($pdoErrorInfo[2]))
            $message = (string)$pdoErrorInfo[2];

        parent::__construct($message);
    }
}