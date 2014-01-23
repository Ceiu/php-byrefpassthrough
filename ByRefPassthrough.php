<?php
/**
 * ByRefPassthrough.php
 * A simple object which attempts to seamlessly redirect calls through to callbacks.
 *
 * @package Cericlabs
 * @subpackage Misc
 *
 * @author Chris "Ceiu" Rog <crog@gustavus.edu>
 */
namespace Cericlabs\Misc;

use ReflectionClass,
    ReflectionFunction;



/**
 * The ByRefPassthrough object is a simple object which attempts to seamlessly redirect calls
 * through to callbacks to test whether or not PHP is properly mapping references between calls.
 *
 * This class defines eight methods of interest, each slightly changing how the callback is
 * executed in an effort to properly execute it while passing arguments both by-ref and by-val.
 *
 * @package Cericlabs
 * @subpackage Misc
 *
 * @author Chris "Ceiu" Rog <crog@gustavus.edu>
 */
abstract class ByRefPassthrough
{
  /**
   * The callback to receive our redirected calls.
   *
   * @var callback
   */
  protected $callback;

////////////////////////////////////////////////////////////////////////////////////////////////////

  /**
   * Creates a new ByRefPassthrough using the specified callback. Protected because we don't want
   * this thing getting allocated directly -- we still have some black magic to do.
   *
   * @param callable $callback
   *  The callback to which calls should be redirected.
   */
  protected function __construct(callable $callback)
  {
    $this->callback = $callback;
  }

  /**
   * Creates a new ByRefPassthrough using the specified callback. This function dynamically creates
   * the necessary subclass for the closure to achieve it's passthrough magic.
   *
   * @param callable $callback
   *  The callback to which calls should be redirected.
   */
  public static final function newInstance(callable $callback)
  {
    $reflection = static::getReflection($callback);

    $plist = '';
    $pcount = 0;

    foreach ($reflection->getParameters() as $param) {
      if ($pcount++) {
        $plist .= ', ';
      }

      // Typehinting
      if ($param->isArray()) {
        $plist .= 'array ';
      } else if ($param->isCallable()) {
        $plist .= 'callable ';
      } else if ($rc = $param->getClass()) {
        $plist .= '\\' . $rc->getName() . ' ';
      }

      // By-ref
      if ($param->isPassedByReference()) {
        $plist .= '&';
      }

      // Name
      $plist .= '$' . $param->getName();

      // Default value
      if ($param->isDefaultValueAvailable()) {
        $value = $param->getDefaultValue();

        if (is_string($value)) {
          $value = '\'' . addslashes($value) . '\'';
        }

        $plist .= ' = ' . ($value ? (string) $value : 'null');
      }
    }

    $rtype = $reflection->returnsReference() ? 'R' : 'V';

    $class = __CLASS__ . "_{$rtype}" . bin2hex($plist);

    return new $class($callback);
  }

////////////////////////////////////////////////////////////////////////////////////////////////////

  /**
   * Retrieves an appropriate reflection object for the current callback.
   */
  protected static function getReflection(callable $callback)
  {
    if (is_array($callback) && count($callback) === 2) {
      $rc = new ReflectionClass($callback[0]);
      return $rc->getMethod($callback[1]);
    }

    return new ReflectionFunction($callback);
  }


  /**
   * Executes the callback using the "traditional" methods of execution: call_user_func_array with
   * func_get_args.
   *
   * This should fail to pass arguments by-ref, but by-val is okay.
   */
  public function doWork()
  {
    return call_user_func_array($this->callback, func_get_args());
  }

  /**
   * Executes the callback using ReflectionFunction's invokeArgs method and func_get_args.
   *
   * This should fail to pass arguments by-ref, but by-val is okay.
   */
  public function doWorkUsingReflection()
  {
    $reflection = static::getReflection($this->callback);
    return ($reflection instanceof ReflectionFunction ? $reflection->invokeArgs(func_get_args()) : $reflection->invokeArgs($this->callback[0], func_get_args()));
  }

  /**
   * Executes the callback using call_user_func_array and debug_backtrace to retrieve the arguments
   * to pass to the callback.
   *
   * This should fail to pass arguments by-ref, but by-val is okay.
   */
  public function doWorkUsingStack()
  {
    $stack = debug_backtrace(0);
    return call_user_func_array($this->callback, $stack[0]['args']);
  }

  /**
   * Executes the callback using ReflectionFunction's invokeArgs method and debug_backtrace.
   *
   * This should fail to pass arguments by-ref, but by-val is okay.
   */
  public function doWorkUsingReflectionWithStack()
  {
    $stack = debug_backtrace(0);

    $reflection = static::getReflection($this->callback);
    $result = ($reflection instanceof ReflectionFunction ? $reflection->invokeArgs($stack[0]['args']) : $reflection->invokeArgs($this->callback[0], $stack[0]['args']));

    return $result;
  }

  /**
   * Executes the callback using the "traditional" methods of execution: call_user_func_array with
   * func_get_args.
   *
   * This should fail to pass arguments by-ref, but by-val is okay.
   */
  public function doWorkWithMagic(&$arg0 = null, &$arg1 = null, &$arg2 = null)
  {
    return call_user_func_array($this->callback, func_get_args());
  }

  /**
   * Executes the callback using ReflectionFunction's invokeArgs method and func_get_args.
   *
   * This should fail to pass arguments by-ref, but by-val is okay.
   */
  public function doWorkUsingReflectionAndMagic(&$arg0 = null, &$arg1 = null, &$arg2 = null)
  {
    $reflection = static::getReflection($this->callback);
    return ($reflection instanceof ReflectionFunction ? $reflection->invokeArgs(func_get_args()) : $reflection->invokeArgs($this->callback[0], func_get_args()));
  }

  /**
   * Executes the callback using call_user_func_array and debug_backtrace to retrieve the arguments
   * to pass to the callback.
   *
   * This method should succeed in passing arguments by-ref if they're within the first three
   * parameters. By-val should work on any parameter.
   */
  public function doWorkUsingStackAndMagic(&$arg0 = null, &$arg1 = null, &$arg2 = null)
  {
    $stack = debug_backtrace(0);
    return call_user_func_array($this->callback, $stack[0]['args']);
  }

  /**
   * Executes the callback using ReflectionFunction's invokeArgs method and debug_backtrace.
   *
   * This method should succeed in passing arguments by-ref if they're within the first three
   * parameters. By-val should work on any parameter.
   */
  public function doWorkUsingReflectionStackAndMagic(&$arg0 = null, &$arg1 = null, &$arg2 = null)
  {
    $stack = debug_backtrace(0);

    $reflection = static::getReflection($this->callback);
    return ($reflection instanceof ReflectionFunction ? $reflection->invokeArgs($stack[0]['args']) : $reflection->invokeArgs($this->callback[0], $stack[0]['args']));
  }

}


/**
 * The classloader we use to generate our dynamic classes.
 */
$result = spl_autoload_register(function($class) {

  // Check if the class matches our expression. This example only has one dynamic function, so our
  // expression is setup accordingly. More complex classes will require something even crazier.
  //
  // Grouping:
  //  0 - Entire match
  //  1 - Namespace
  //  2 - Unqualified class name
  //  3 - The base class name (ByRefPassthrough, in this case)
  //  4 - Return type (reference or value)
  //  5 - Encoded parameter list
  if (preg_match('/\\A(Cericlabs\\\\Misc)\\\\((ByRefPassthrough)_(R|V)([A-Za-z0-9+\\/]*))\\z/', $class, $matches)) {
    // First, pull the param list out of the classname and decode it.
    $plist = pack("H*", $matches[5]);

    // Next, we build our dynamic subclass using the param list.
    // Note:
    // This currently does not support passing through values returned by reference. I may add it
    // as time permits; but if you're impatient, a solution that can work involves rebuilding the
    // parameter list and calling eval from within the to-be-eval'd string below.
    $subclass = sprintf(
      'namespace %s {
        class %s extends %s {
          public function %s__invoke(%s) {
            $stack = debug_backtrace(0);

            $reflection = static::getReflection($this->callback);
            $result = ($reflection instanceof \\ReflectionFunction ? $reflection->invokeArgs($stack[0][\'args\']) : $reflection->invokeArgs($this->callback[0], $stack[0][\'args\']));

            return $result;
          }
        }
      }',

      $matches[1], $matches[2], $matches[3], ($matches[4] === 'R' ? '&' : ''), $plist
    );

    // Eval!
    eval($subclass);

    // At this point, the subclass should be defined properly and should be able to act as a
    // transparent passthrough.
  }
}, true, true);
