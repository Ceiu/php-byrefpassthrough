php-byrefpassthrough
====================

The ByRefPassthrough is a hack I put together to address an issue PHP has when it comes to, as the title implies, passing parameters by-reference dynamically through to callbacks and other such handlers.

The source files mearly demonstrate the hack and test that the traditional methods do not work and that the hack does, without adding other negative side effects (performance concerns aside).


The remainder of this readme serves as an example and explaination of the hack and the problems it works around.


The by-ref passthrough hack
===========================
The by-ref passthrough hack is something I threw together to solve a problem of passing references to functions and methods that offload their work to other functions and callbacks.
Since passing by-ref is somewhat new to PHP and is often frowned upon, most developers don't even happen upon the issue, so the "traditional" passthrough works just fine. But there are several cases where passing by-value just doesn't work.


"Traditional" passthrough:
```php
function doWork() {
  // Callback can be any callable type: Closure, method, etc.
  $callback = /* insert proper callable here */; 

  // Get the arguments as an array.  
  $args = func_get_args();
  
  // Pass them through to our callback!
  return call_user_func_array($callback, $args);
}
```

As stated above: in most cases, this works fine. However, if the callback accepts or expects parameters to be passed by-ref, this will fail (occasionally throwing a warning). There are a couple stumbling blocks here that get in the way:

1) ```func_get_args``` returns a copy of the arguments, breaking any references.
We can examine this by looking at the zval dump in the passthrough:

```php
function callbackHandler(&$arg1, $arg2) {
  $comb = "{$arg1}{$arg2}";
  $arg1 = 'arg1 @ callback';
  $arg2 = 'arg2 @ callback';

  return $comb;
}

function doWork() {
  // Callback can be any callable type: Closure, method, etc.
  $callback = 'callbackHandler';

  // Get the arguments as an array.
  $args = func_get_args();

  // Take a closer look at our arguments.
  debug_zval_dump($args);

  // Pass them through to our callback!
  return call_user_func_array($callback, $args);
}

$var1 = 'one';
$var2 = 'two';

$result = doWork($var1, $var2);

var_dump($result, $var1, $var2);
```

The first thing you'll notice here is that PHP is throwing a warning because we pass a value where it wanted a reference (whoops). But, more importantly, the output of the $args zval dump shows that the arguments have no reference back to their source.

```
array(2) refcount(2) {
  [0]=> string(3) "one" refcount(1)
  [1]=> string(3) "two" refcount(1) 
} 
```

If we can't address that, there's no hope at all. Fortunately, there's a handy workaround: ```debug_backtrace```. Using debug_backtrace, we can get the arguments as they were presented to the callee:

```php
$stack = debug_backtrace(0, 1);
$args = $stack[0]['args'];
```

```debug_backtrace``` returns all sorts of information about each call, so it's not exactly the most efficient tool in PHP. We can somewhat mitigate the performance hit by not including the objects and limiting the returned stack trace to one frame by passing 1 for the limit.

Once we have our frame, we can pull out the arguments array. Give it another run and you should see this:

```
array(2) refcount(3) {
  [0]=> &string(3) "one" refcount(2)
  [1]=> &string(3) "two" refcount(2)
}

string 'onetwo' (length=6)
string 'one' (length=3)
string 'two' (length=3)
```

Much better! And that warning is gone, too! But, it still didn't modify our variables. More digging is necessary...


2) The much harder, and far more sinister, issue to find is how PHP deals with function calls internally. As it stands now, doWork is being called without any *\_BY\_REF attributes on the arguments, because we haven't defined any parameters (nevermind any that would tell PHP to pass by-reference).

I've not ventured back into the depths of PHP's internals since writing [GTO](https://github.com/Gustavus/php-gto) (and, to be frank, I have no desire to do so again anytime soon), but from what I remember and can determine from what's happening here, _PHP does not care about references unless explicitly told to do so_.

We can work around this issue by simply adding a bunch of optional dummy references to the ```doWork``` signature:

```php
function doWork(&$arg0 = null, &$arg1 = null, &$arg2 = null) {
  ...
```

This tells PHP to pass the first three by-reference, or throw a reference to null in the argument list. While far from perfect, it will force PHP to retain to reference to the original variable, allowing the callback's changes to propagate as we'd expect:

```
array(2) refcount(3){ 
  [0]=> &string(3) "one" refcount(2)
  [1]=> &string(3) "two" refcount(2)
}

string 'onetwo' (length=6)
string 'arg1 @ callback' (length=15)
string 'two' (length=3)
```

Notice that, while we passed ```$var2``` to the passthrough by-reference, it was not passed to the callback as such; so our variable remained unchanged.

You may have also noticed that our zval dump, annoyingly, didn't change as one may have expected (grumble grumble...). The implication here is that PHP's pass-by-reference mechanics are not implemented by sharing zvals. Though, as I stated above, I've not dug back in to verify this -- it's simply my best guess given known, repeatable behavior and data.


Anyway, ~~the only real~~ one drawback to this hack is that each parameter must be explicitly listed to be passed by-ref. With the final signature on doWork, we could pass 100 parameters, but only the first three will be recognized as references. Also, it's incredibly ugly and convoluting to add a bunch of parameters because something *might* need them in the future. **The biggest drawback, however, is that you can no longer use terminals in the call to the passthrough, and using expressions results in a strict-standards warning:**

```php
$result = doWork(1, 2); // Fatal error

$result = doWork(strrev('hello'), strrev('dolly')); // Strict standards warning.
```

This may be a dealbreaker for some, but if you're feeling particularly sadistic, there may be a workaround for you.


Dynamically generating method signatures
========================================

In certain cases, it may be possible to (mis)use some PHP implementation details to work around the
method signature issue and generate it on the fly. This, of course, comes with its own overhead and
limitations, so it definitely won't be applicable everywhere.

Requirements:
* The class which requires the dynamic method must be created by a factory.
* The callback must be passed to the factory's build method/function.
* The callback must be (mostly) immutable.
* eval must be available.
* Your project/team must allow flexibility when it comes to code sanity. This is not pretty.

If any of these present a problem, this workaround will likely cause more problems than it solves. If not, read on.

The trick we'll be using is to generate a subclass of our desired class which contains the method with the desired signature *per callback*. By parsing the callback upon object construction, we can generate a dynamic class name containing the method signature. This allows us to reuse the class and even serialize/unserialize it later.


The components
--------------
* The afore mentioned required factory function
* A classloader
* A bottle of your favorite alcoholic beverage


The first component in this unholy hack is the factory function which will generate subclasses with our desired method. You can use any factory pattern with which you're comfortable, but we'll be using a static function in our example. As stated above, the factory must receive the callback to execute as it builds the new object. Once the object is built, we'd need to dive into the symbol table to make any changes (which is not only a bad idea, but not possible from user land).

With the callback, we can use PHP's reflection classes to figure out what the callback is expecting for input. Unfortunately, we have to do some fairly tedious work to translate the information from the reflection object back to PHP code.

```php
  public static final function newInstance(callable $callback)
  {
    // Get a ReflectionMethod or ReflectionFunction, depending on the source
    // of our callback.
    if (is_array($callback) && count($callback) === 2) {
      $rc = new ReflectionClass($callback[0]);
      $reflection = $rc->getMethod($callback[1]);
    } else {
      $reflection = new ReflectionFunction($callback);
    }

    $plist = '';
    $pcount = 0;
    
    // Process the parameter list...
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
        // We need to add a leading slash here to ensure PHP
        // processes it as an absolute class name.
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
    
    // Is the callback going to give us a reference?
    $rtype = $reflection->returnsReference() ? 'R' : 'V';
    
    // Build the name for our dynamic class.
    // We use bin2hex here to encode away invalid characters.
    $class = __CLASS__ . "_{$rtype}" . bin2hex($plist);
    
    // Instantiate! This will likely cause our class to be loaded.
    return new $class($callback);
  }
```

Yuck. There are likely better ways to do this, but the goal here is to produce the parameter list and then encode it into the new classname. What we have above will generate names in the form of: 

  <FullyQualifiedClassName>_<ReturnType><EncodedParameterList>
  
Where:
* <FullyQualifiedClassName> is exactly what it says on the tin: The fully qualified class name. If your class is in the namespace A\B and is named Foo, this will be A\B\Foo.
* <ReturnType> is a single character representing the type (reference or value) of value the callback is going to return. If it returns by-reference, this will be an R. Otherwise, it's a V.
* <EncodedParameterList> is a lengthy hexadecimal string containing the encoded parameter list.


Now, as I'm sure you've noticed, that last line will cause our goofy-looking class to be loaded immediately. At this point, we need a classloader that can process that mess. This actually isn't as bad as it sounds. Since we know what class we'll be looking for, we can use a regular expression to parse out everything we need:

```php
$result = spl_autoload_register(function($class) {

  // Check if the class matches our expression. This example expects the class to be in the
  // "Cericlabs\Misc" namespace with a base name of "ByRefPassthrough", so you'll likely
  // need to change it accordingly.
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
```

This classloader attempts to generate classes for anything matching the form we've defined above. Either of these can change, so long as the factory generates names the classloader is expecting to see. The key here is that the encoded parameter list is part of the classname and can be parsed easily. This bit is crucial if we want to be able to recover or regenerate the class after unserializing or some such thing.

In this case, we're generating the parameter list for the __invoke method. The actual method name, and its contents, are of no consequence here -- they're simply what we're generating for this particular implementation/example. Each project will require its own method(s) and code here.

Anyway, with these two in place, we end up with a method that has a signature matching that of our callbacks. Passing in values by-ref and by-value will work as expect *and* we can use literals and variables as we'd expect to. Glorious.

The only hiccup remaining is return by-ref. Currently, all of PHP's call forwarding functionality returns the result by-value, which gives us the same problem with far fewer avenues of attack. There is a way to deal with it by generating code for the reflected call into a string and ```eval```ing it, but that seems a bit much -- even for me. I'm drawing the line at recursive ```eval```s and leaving that as an exercise for those who truly need it.


Final Thoughts
--------------
I know I've been saying it and hinting at it a lot through this readme, but I feel it can't be said enough: This is a hack; and one to workaround something that only affects a handful of edge cases. The complexity and overhead associated with this is probably not worth it if you're in a position to refactor the codebase to simply not be passing values by-ref through to callbacks and closures.

Additionally, I had to resist the temptation to demo this workaround using features found in vanilla PHP. There are several libraries and frameworks which resolve this problem in much cleaner ways or give it much higher usability potential. In particular, [nikic](https://github.com/nikic)'s [PHPParser](https://github.com/nikic/PHP-Parser) would likely clean up a lot of the code generation I'm doing in the classloader and may improve the parameter scanner as well. My own project, [Gustavus Temporary Overrides](https://github.com/Gustavus/php-gto), could also be used in concert with the dynamic class generation to do method replacement by injecting the callback into the class's symbol table directly. 

In any event, PHP doesn't make this an easy task. Hopefully in the future they'll improve the whole by-ref passing/returning thing, or, at the very least, improve the reflection and forwarding tools to allow these things to be done with far fewer headaches. In the meantime, we are left evaluating whether or not by-ref passthrough of non-objects is worth enough the trouble required by these workarounds.

-C
