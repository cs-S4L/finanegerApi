<?php
define("DIR__ROOT", dirname(__DIR__) . DIRECTORY_SEPARATOR);

require(DIR__ROOT . "config/config.php");
require(DIR__ROOT . "src/core/utility.php");

if ($_SERVER["SERVER_ADDR"] === "127.0.0.1" || $_SERVER["SERVER_ADDR"] === "::1") {
    define("SERVER_MODE", "DEV");
} else {
    define("SERVER_MODE", "PRODUCTION");
}

if (SERVER_MODE === "PRODUCTION") {
    error_reporting(E_ERROR);
    ini_set("display_errors", "0");
} else {
    error_reporting(E_ALL);
    ini_set("display_errors", "1");
}

spl_autoload_register(array("Utility", "autoload"));