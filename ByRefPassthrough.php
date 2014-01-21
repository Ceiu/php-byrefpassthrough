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
class ByRefPassthrough
{
  /**
   * The callback to receive our redirected calls.
   *
   * @var callbakc
   */
  protected $callback;

////////////////////////////////////////////////////////////////////////////////////////////////////

  /**
   * Creates a new ByRefPassthrough using the specified
   *
   */
  public function __construct(callable $callback)
  {
    $this->callback = $callback;
  }

////////////////////////////////////////////////////////////////////////////////////////////////////

  /**
   * Retrieves an appropriate reflection object for the current callback.
   */
  protected function getReflection()
  {
    if (is_array($this->callback)) {
      $rc = new ReflectionClass($this->callback[0]);
      return $rc->getMethod($this->callback[1]);
    }

    return new ReflectionFunction($this->callback);
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
    $reflection = $this->getReflection();
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

    $reflection = $this->getReflection();
    return ($reflection instanceof ReflectionFunction ? $reflection->invokeArgs($stack[0]['args']) : $reflection->invokeArgs($this->callback[0], $stack[0]['args']));
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
    $reflection = $this->getReflection();
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

    $reflection = $this->getReflection();
    return ($reflection instanceof ReflectionFunction ? $reflection->invokeArgs($stack[0]['args']) : $reflection->invokeArgs($this->callback[0], $stack[0]['args']));
  }

}
