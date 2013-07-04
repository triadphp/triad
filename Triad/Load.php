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

define("TRIAD_DIR", __DIR__);
define("TRIAD", true);
define("TRIAD_VERSION", "1.0");

ignore_user_abort(1);

// handle php errors as exceptions
set_error_handler(function ($code, $message, $file, $line) {
    throw new \ErrorException($message, $code, 0, $file, $line);
});

require(TRIAD_DIR . "/Application.php");
require(TRIAD_DIR . "/RemoteApplication.php");
require(TRIAD_DIR . "/Config.php");
require(TRIAD_DIR . "/Presenter.php");
require(TRIAD_DIR . "/Database.php");
require(TRIAD_DIR . "/Autoload.php");
require(TRIAD_DIR . "/Exceptions.php");
require(TRIAD_DIR . "/Utils.php");
require(TRIAD_DIR . "/Request.php");
require(TRIAD_DIR . "/Response.php");
require(TRIAD_DIR . "/Router.php");
require(TRIAD_DIR . "/Session.php");
require(TRIAD_DIR . "/Model.php");

require(TRIAD_DIR . "/Requests/HttpRequest.php");

require(TRIAD_DIR . "/Responses/HttpResponse.php");
require(TRIAD_DIR . "/Responses/JsonResponse.php");
require(TRIAD_DIR . "/Responses/PhpSerializeResponse.php");
require(TRIAD_DIR . "/Responses/RedirectResponse.php");
require(TRIAD_DIR . "/Responses/RawResponse.php");

require(TRIAD_DIR . "/Exceptions/DatabaseException.php");
require(TRIAD_DIR . "/Exceptions/RemoteException.php");
require(TRIAD_DIR . "/Exceptions/NotFoundException.php");
require(TRIAD_DIR . "/Exceptions/ValidationException.php");

