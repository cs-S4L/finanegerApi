<?php 
/**
 * Copied and altert from Sae-Lessons
 */

/** @noinspection PhpUndefinedMethodInspection */
/** @noinspection PhpIncludeInspection */
/** @noinspection PhpUnhandledExceptionInspection */
class Utility
{
    public static function autoload($classString)
    {
        $last_backslash_pos = strrpos($classString, "\\") + 1;
        $namespace = substr($classString, 0, $last_backslash_pos);
        $className = substr($classString, $last_backslash_pos);

        $classFileName = strtolower($className) . ".php";
        $namespace = ltrim($namespace, "\\");

        $namespace = str_replace("\\", DIRECTORY_SEPARATOR, $namespace);
        $classFileName = str_replace("_", DIRECTORY_SEPARATOR, $classFileName);

        $classFilePath = DIR__ROOT . $namespace . $classFileName;

        require($classFilePath);
    }

    // public static function handleException(Throwable $exception) {
    //     if (ini_get("display_errors") === "1") {
    //         $exception->displayException();
    //     } else {
    //         echo "Ups, da ist wohl was schiefgelaufen! Der Techniker ist informiert.";
    //     }
    // }
}