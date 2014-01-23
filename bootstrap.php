<?php
/**
 * bootstrap.php
 *
 * Contains the classloader for this package and any other initialization goodies.
 */

abstract class CLBootstrap
{
  /**
   * Class autoloader function.
   */
  public static function loadClass($class)
  {
    if (empty($class) || !is_string($class)) {
      throw new InvalidArgumentException('$class is null, empty or not a string.');
    }

    if (preg_match('/\\ACericlabs\\\\Misc\\\\(.+)\\z/', $class, $matches)) {
      $target = __DIR__ . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $matches[1]) . '.php';

      if (is_readable($target)) {
        require_once($target);
      }
    }
  }
}


spl_autoload_register('CLBootstrap::loadClass', true, false);

