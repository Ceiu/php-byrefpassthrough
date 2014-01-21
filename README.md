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

In any event, this entire problem will likely be resolved by the addition of variadic functions, whenever that rolls out. Until then, we have this ugly hack to workaround this issue and do those things the PHP manual tells us not to do anyway.

-C
