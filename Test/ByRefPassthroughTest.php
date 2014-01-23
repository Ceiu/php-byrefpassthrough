<?php
/**
 * ByRefPassthroughTest.php
 *
 * @package Cericlabs
 * @subpackage Misc
 *
 * @author Chris "Ceiu" Rog <ceiu@cericlabs.com>
 */
namespace Cericlabs\Misc\Test;

use PHPUnit_Framework_TestCase,
    Cericlabs\Misc\ByRefPassthrough;



/**
 * Tests for passing by-ref parameters through reflected function/method invocations.
 *
 * @author Chris "Ceiu" Rog <ceiu@cericlabs.com>
 */
class ByRefPassthroughTest extends PHPUnit_Framework_TestCase
{
  /**
   * The closure used during closure tests. Initialized by the SetUpBeforeClass function.
   *
   * @var Closure
   */
  protected static $closure;

  /**
   * The reference returned by our callbackHandlerWithReference callback.
   *
   * @var mixed
   */
  protected $reference;


  /**
   * Non-closure callback handler. Used during the callback tests.
   */
  public function callbackHandler(&$arg0 = null, $arg1 = null)
  {
    $comb = "{$arg0}{$arg1}";

    $arg0 = "arg0 @ callback: {$arg0}";
    $arg1 = "arg1 @ callback: {$arg1}";

    return $comb;
  }

  /**
   * Non-closure callback that returns a reference. Used to ensure our magic invoke passes
   * references through as well.
   */
  public function &callbackHandlerWithReference()
  {
    return $this->reference;
  }

////////////////////////////////////////////////////////////////////////////////////////////////////

  /**
   * PHPUnit pseudo-static initializer.
   */
  public static function SetUpBeforeClass()
  {
    static::$closure = function(&$arg0 = null, $arg1 = null) {
      $comb = "{$arg0}{$arg1}";

      $arg0 = "arg0 @ closure: {$arg0}";
      $arg1 = "arg1 @ closure: {$arg1}";

      return $comb;
    };
  }

////////////////////////////////////////////////////////////////////////////////////////////////////

  /**
   * @test
   */
  public function testDoWorkWithCallback()
  {
    //$callback = [$this, 'callbackHandler'];
    $callback = static::$closure;
    //$callback = [new \Jeremeamia\SuperClosure\SerializableClosure(function() {}), '__construct'];

    $obj = ByRefPassthrough::newInstance($callback);
    $var1 = 'one';
    $var2 = 'two';

    $result = $obj->doWork($var1, $var2);

    $this->assertEquals('onetwo', $result);
    $this->assertEquals('one', $var1);
    $this->assertEquals('two', $var2);
  }

  /**
   * @test
   */
  public function testDoWorkWithClosure()
  {
    $callback = static::$closure;

    $obj = ByRefPassthrough::newInstance($callback);
    $var1 = 'one';
    $var2 = 'two';

    $result = $obj->doWork($var1, $var2);

    $this->assertEquals('onetwo', $result);
    $this->assertEquals('one', $var1);
    $this->assertEquals('two', $var2);
  }

  /**
   * @test
   */
  public function testDoWorkUsingReflectionWithCallback()
  {
    $callback = [$this, 'callbackHandler'];

    $obj = ByRefPassthrough::newInstance($callback);
    $var1 = 'one';
    $var2 = 'two';

    $result = $obj->doWorkUsingReflection($var1, $var2);

    $this->assertEquals('onetwo', $result);
    $this->assertEquals('one', $var1);
    $this->assertEquals('two', $var2);
  }

  /**
   * @test
   */
  public function testDoWorkUsingReflectionWithClosure()
  {
    $callback = static::$closure;

    $obj = ByRefPassthrough::newInstance($callback);
    $var1 = 'one';
    $var2 = 'two';

    $result = $obj->doWorkUsingReflection($var1, $var2);

    $this->assertEquals('onetwo', $result);
    $this->assertEquals('one', $var1);
    $this->assertEquals('two', $var2);
  }

  /**
   * @test
   */
  public function testDoWorkUsingStackWithCallback()
  {
    $callback = [$this, 'callbackHandler'];

    $obj = ByRefPassthrough::newInstance($callback);
    $var1 = 'one';
    $var2 = 'two';

    $result = $obj->doWorkUsingStack($var1, $var2);

    $this->assertEquals('onetwo', $result);
    $this->assertEquals('one', $var1);
    $this->assertEquals('two', $var2);
  }

  /**
   * @test
   */
  public function testDoWorkUsingStackWithClosure()
  {
    $callback = static::$closure;

    $obj = ByRefPassthrough::newInstance($callback);
    $var1 = 'one';
    $var2 = 'two';

    $result = $obj->doWorkUsingStack($var1, $var2);

    $this->assertEquals('onetwo', $result);
    $this->assertEquals('one', $var1);
    $this->assertEquals('two', $var2);
  }

  /**
   * @test
   */
  public function testDoWorkUsingReflectionWithStackWithCallback()
  {
    $callback = [$this, 'callbackHandler'];

    $obj = ByRefPassthrough::newInstance($callback);
    $var1 = 'one';
    $var2 = 'two';

    $result = $obj->doWorkUsingReflectionWithStack($var1, $var2);

    $this->assertEquals('onetwo', $result);
    $this->assertEquals('one', $var1);
    $this->assertEquals('two', $var2);
  }

  /**
   * @test
   */
  public function testDoWorkUsingReflectionWithStackWithClosure()
  {
    $callback = static::$closure;

    $obj = ByRefPassthrough::newInstance($callback);
    $var1 = 'one';
    $var2 = 'two';

    $result = $obj->doWorkUsingReflectionWithStack($var1, $var2);

    $this->assertEquals('onetwo', $result);
    $this->assertEquals('one', $var1);
    $this->assertEquals('two', $var2);
  }

  /**
   * @test
   */
  public function testDoWorkWithMagicWithCallback()
  {
    $callback = [$this, 'callbackHandler'];

    $obj = ByRefPassthrough::newInstance($callback);
    $var1 = 'one';
    $var2 = 'two';

    $result = $obj->doWorkWithMagic($var1, $var2);

    $this->assertEquals('onetwo', $result);
    $this->assertEquals('one', $var1);
    $this->assertEquals('two', $var2);
  }

  /**
   * @test
   */
  public function testDoWorkWithMagicWithClosure()
  {
    $callback = static::$closure;

    $obj = ByRefPassthrough::newInstance($callback);
    $var1 = 'one';
    $var2 = 'two';

    $result = $obj->doWorkWithMagic($var1, $var2);

    $this->assertEquals('onetwo', $result);
    $this->assertEquals('one', $var1);
    $this->assertEquals('two', $var2);
  }

  /**
   * @test
   */
  public function testDoWorkUsingReflectionAndMagicWithCallback()
  {
    $callback = [$this, 'callbackHandler'];

    $obj = ByRefPassthrough::newInstance($callback);
    $var1 = 'one';
    $var2 = 'two';

    $result = $obj->doWorkUsingReflectionAndMagic($var1, $var2);

    $this->assertEquals('onetwo', $result);
    $this->assertEquals('one', $var1);
    $this->assertEquals('two', $var2);
  }

  /**
   * @test
   */
  public function testDoWorkUsingReflectionAndMagicWithClosure()
  {
    $callback = static::$closure;

    $obj = ByRefPassthrough::newInstance($callback);
    $var1 = 'one';
    $var2 = 'two';

    $result = $obj->doWorkUsingReflectionAndMagic($var1, $var2);

    $this->assertEquals('onetwo', $result);
    $this->assertEquals('one', $var1);
    $this->assertEquals('two', $var2);
  }

  /**
   * @test
   */
  public function testDoWorkUsingStackAndMagicWithCallback()
  {
    $callback = [$this, 'callbackHandler'];

    $obj = ByRefPassthrough::newInstance($callback);
    $var1 = 'one';
    $var2 = 'two';

    $result = $obj->doWorkUsingStackAndMagic($var1, $var2);

    $this->assertEquals('onetwo', $result);
    $this->assertEquals('arg0 @ callback: one', $var1);
    $this->assertEquals('two', $var2);
  }

  /**
   * @test
   */
  public function testDoWorkUsingStackAndMagicWithClosure()
  {
    $callback = static::$closure;

    $obj = ByRefPassthrough::newInstance($callback);
    $var1 = 'one';
    $var2 = 'two';

    $result = $obj->doWorkUsingStackAndMagic($var1, $var2);

    $this->assertEquals('onetwo', $result);
    $this->assertEquals('arg0 @ closure: one', $var1);
    $this->assertEquals('two', $var2);
  }

  /**
   * @test
   */
  public function testDoWorkUsingReflectionStackAndMagicWithCallback()
  {
    $callback = [$this, 'callbackHandler'];

    $obj = ByRefPassthrough::newInstance($callback);
    $var1 = 'one';
    $var2 = 'two';

    $result = $obj->doWorkUsingReflectionStackAndMagic($var1, $var2);

    $this->assertEquals('onetwo', $result);
    $this->assertEquals('arg0 @ callback: one', $var1);
    $this->assertEquals('two', $var2);
  }

  /**
   * @test
   */
  public function testDoWorkUsingReflectionStackAndMagicWithClosure()
  {
    $callback = static::$closure;

    $obj = ByRefPassthrough::newInstance($callback);
    $var1 = 'one';
    $var2 = 'two';

    $result = $obj->doWorkUsingReflectionStackAndMagic($var1, $var2);

    $this->assertEquals('onetwo', $result);
    $this->assertEquals('arg0 @ closure: one', $var1);
    $this->assertEquals('two', $var2);
  }


  /**
   * @test
   */
  public function testMagicInvokeWithCallback()
  {
    $callback = [$this, 'callbackHandler'];

    $obj = ByRefPassthrough::newInstance($callback);
    $var1 = 'one';
    $var2 = 'two';

    $result = $obj($var1, $var2);

    $this->assertEquals('onetwo', $result);
    $this->assertEquals('arg0 @ callback: one', $var1);
    $this->assertEquals('two', $var2);
  }

  /**
   * @test
   */
  public function testMagicInvokeWithClosure()
  {
    $callback = static::$closure;

    $obj = ByRefPassthrough::newInstance($callback);
    $var1 = 'one';
    $var2 = 'two';

    $result = $obj($var1, $var2);

    $this->assertEquals('onetwo', $result);
    $this->assertEquals('arg0 @ closure: one', $var1);
    $this->assertEquals('two', $var2);
  }

  /**
   * @test
   */
  public function testMagicInvokeWithCallbackAndLiterals()
  {
    $callback = [$this, 'callbackHandler'];

    $obj = ByRefPassthrough::newInstance($callback);
    $var1 = 'one';

    $result = $obj($var1, 'two');

    $this->assertEquals('onetwo', $result);
    $this->assertEquals('arg0 @ callback: one', $var1);
  }

  /**
   * @test
   */
  public function testMagicInvokeWithClosureAndLiterals()
  {
    $callback = static::$closure;

    $obj = ByRefPassthrough::newInstance($callback);
    $var1 = 'one';

    $result = $obj($var1, 'two');

    $this->assertEquals('onetwo', $result);
    $this->assertEquals('arg0 @ closure: one', $var1);
  }

  /**
   * @test
   */
  public function testMagicInvokeWithComplexSignature()
  {
    $ref = 'steak';
    $callback = function(ByRefPassthroughTest $arg0, &$arg1, array $arg2, &$arg3 = '\'bac\\\'on\'', $arg4 = null, $arg5 = '"eggs"') use (&$ref) {
      $arg1 = 'arg1 @ closure';
      $arg4 = 'arg4 @ closure';

      return $ref;
    };

    $obj = ByRefPassthrough::newInstance($callback);
    $var1 = 'one';
    $var2 = 'two';
    $var3 = 'three';

    $result = $obj($this, $var1, [1, 'array', 'literal!'], $var2, $var3);

    $this->assertEquals('steak', $result);
    $this->assertEquals('arg1 @ closure', $var1);
    $this->assertEquals('three', $var3);
  }

  /**
   * @test
   */
  public function testMagicInvokeReturnsReference()
  {
    $this->markTestSkipped('This will be enabled once return by-ref support is added.');
    return;



    $callback = [$this, 'callbackHandlerWithReference'];

    $obj = ByRefPassthrough::newInstance($callback);

    $this->reference = 'primed';
    $result =& $obj();

    $this->assertEquals('primed', $result);

    $result = 'changed';
    $this->assertEquals('changed', $this->reference);
  }

}