<?php

// load php unit
require_once("PHPUnit/Autoload.php");
require_once("config.php");

// internal paths
define("APP_DIR", __DIR__ . "/../../app");
define("LIBS_DIR", __DIR__ . "/../../libs");
define("TMP_DIR", __DIR__ . "/../../tmp");

// load framework
require_once(LIBS_DIR . "/Triad/Load.php");

use \Triad\Config;
use \Triad\Autoload;
use \Triad\Router;
use \Triad\Application;
use \Triad\Database;

// autoloader
Autoload::register();
Autoload::add(APP_NAMESPACE, APP_DIR);

