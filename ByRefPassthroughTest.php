<?php
/**
 * ByRefPassthroughTest.php
 *
 * @package Cericlabs
 * @subpackage Misc
 *
 * @author Chris "Ceiu" Rog <ceiu@cericlabs.com>
 */
namespace Cericlabs\Misc;

require_once(__DIR__ . DIRECTORY_SEPARATOR . 'ByRefPassthrough.php');

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
   * Non-closure callback handler. Used during the callback tests.
   */
  public function callbackHandler(&$arg0 = null, $arg1 = null)
  {
    $comb = "{$arg0}{$arg1}";

    $arg0 = "arg0 @ callback: {$arg0}";
    $arg1 = "arg1 @ callback: {$arg1}";

    return $comb;
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
    $callback = [$this, 'callbackHandler'];

    $obj = new ByRefPassthrough($callback);
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

    $obj = new ByRefPassthrough($callback);
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

    $obj = new ByRefPassthrough($callback);
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

    $obj = new ByRefPassthrough($callback);
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

    $obj = new ByRefPassthrough($callback);
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

    $obj = new ByRefPassthrough($callback);
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

    $obj = new ByRefPassthrough($callback);
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

    $obj = new ByRefPassthrough($callback);
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

    $obj = new ByRefPassthrough($callback);
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

    $obj = new ByRefPassthrough($callback);
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

    $obj = new ByRefPassthrough($callback);
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

    $obj = new ByRefPassthrough($callback);
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

    $obj = new ByRefPassthrough($callback);
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

    $obj = new ByRefPassthrough($callback);
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

    $obj = new ByRefPassthrough($callback);
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

    $obj = new ByRefPassthrough($callback);
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
  // public function testDoWorkUsingMagicWithTerminals()
  // {
  //   $callback = static::$closure;

  //   $obj = new ByRefPassthrough($callback);

  //   $result = $obj->doWorkWithMagic(1, 2);

  //   $this->assertEquals('12', $result);
  // }

  /**
   * @test
   */
  // public function testDoWorkUsingMagicWithReturnedValue()
  // {
  //   $callback = static::$closure;

  //   $obj = new ByRefPassthrough($callback);

  //   $result = $obj->doWorkUsingStackAndMagic(strrev('one'), strrev('two'));

  //   $this->assertEquals('enoowt', $result);
  // }


}