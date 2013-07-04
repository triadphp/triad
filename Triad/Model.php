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

abstract class Model {
    /**
     * @var \Triad\Database
     */
    protected $db;

    public function __construct(\Triad\Database $db) {
        $this->db = $db;
    }
}
