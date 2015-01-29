precore
=======
[![Latest Stable Version](https://poser.pugx.org/precore/precore/v/stable.png)](https://packagist.org/packages/precore/precore)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/szjani/precore/badges/quality-score.png?b=1.8)](https://scrutinizer-ci.com/g/szjani/precore/?branch=1.8)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/f0097752-37c5-44a2-96e8-3304ca687f67/mini.png)](https://insight.sensiolabs.com/projects/f0097752-37c5-44a2-96e8-3304ca687f67)
[![Gitter chat](https://badges.gitter.im/szjani/precore.png)](https://gitter.im/szjani/precore)

|master|1.9|
|------|---|
|[![Build Status](https://travis-ci.org/szjani/precore.png?branch=master)](https://travis-ci.org/szjani/precore)|[![Build Status](https://travis-ci.org/szjani/precore.png?branch=1.9)](https://travis-ci.org/szjani/precore)|
|[![Coverage Status](https://coveralls.io/repos/szjani/precore/badge.png?branch=master)](https://coveralls.io/r/szjani/precore?branch=master)|[![Coverage Status](https://coveralls.io/repos/szjani/precore/badge.png?branch=1.9)](https://coveralls.io/r/szjani/precore?branch=1.9)|

Precore is a common library which based on ideas coming from the Java world.

1. [Object and ObjectClass](https://github.com/szjani/precore#1-object-and-objectclass)
2. [Enum](https://github.com/szjani/precore#2-enum)
3. [Error handling](https://github.com/szjani/precore#3-error-handling)
4. [Object utilities](https://github.com/szjani/precore#4-object-utilities)
5. [Preconditions](https://github.com/szjani/precore#5-preconditions)
6. [Stopwatch](https://github.com/szjani/precore#6-stopwatch)
7. [Profiler](https://github.com/szjani/precore#7-profiler)
8. [Collections](https://github.com/szjani/precore#8-collections)

For more information, click on the items. If you need even more information, check the phpdoc.

1. Object and ObjectClass
-------------------------

In Java, all objects are implicitly extend the `Object` class. It is really convenient since some common methods are defined for all objects.
This behavior is missing from PHP, therefore precore provides `precore\lang\Object`. Sometimes it is required to be able to enforce in an interface, that
the implementation must be an `Object`, thus `precore\lang\ObjectInterface` can be used for that.

* `Object::objectClass()` static function, returns the `ObjectClass` for the particular class
* `Object::getObjectClass()` the same, but non-static method
* `Object::className()` the same as `AnyClass::class` in PHP 5.5
* `Object::getClassName()` returns the class name of the actual object
* `Object::hashCode()` returns `spl_object_hash($this)`
* `Object::equals(ObjectInterface $object)` returns true if the actual object and the argument are equal
* `Object::toString()` and `Object::__toString()`: both return the string representation of the object (the default format is: `{className}@{hashCode}`)
* `Object::getLogger()` retrieves an [lf4php](https://github.com/szjani/lf4php) logger object for the actual class

The `ObjectClass` class extends `ReflectionClass` and gives some more features. These objects are cached if we get them through `ObjectClass::forName($className)` function.
`ObjectClass` also supports resources (almost as in Java) in case of classes follow PSR-0.

2. Enum
-------

In PHP unfortunately there is no Enum. `precore\lang\Enum` is an abstract class which tries to solve this lack of feature. Our enum class must extends this class
and all possible values must be defined as public static variables which will be automatically found and initialized by precore.

```php
final class Color extends Enum
{
    public static $RED;
    public static $GREEN;
    public static $BLUE;
}
Color::init();

function printName(Color $color) {
    echo $color->name() . PHP_EOL;
}

printName(Color::$RED);
foreach (Color::values() as $color) {
    printName($color);
}
```

It produces the following output:

```
RED
RED
GREEN
BLUE
```

3. Error handling
-----------------

In several cases, PHP trigger errors instead of exceptions. Precore can automatically handle these errors and convert them into exception. The only thing should be done:

```php
ErrorHandler::register();
```

After that, we will be able to catch specific exceptions. For the available exceptions see `precore\util\error` namespace.

4. Object utilities
-------------------

### ToStringHelper

Creating a string representation of an object is important, but not an exciting thing and we always need to use almost the same boilerplate code. With `precore\util\ToStringHelper`
it can be simplified. A new instance can be created through `Objects::toStringHelper()` as well.

```php
namespace HelloWorld;

class Foo {
    private $bar = 'foobar';
    
    public function __toString()
    {
        return Objects::toStringHelper($this)
            ->add('bar', $this->bar)
            ->toString();
    }
}

echo (string) new Foo();
// prints 'HelloWorld\Foo{bar=foobar}'
```

It supports arrays and `DateTime` as well. If the `ErrorHandler` is registered, `spl_object_hash()` will be used for those objects which cannot be cast to string.

### Equality

Two variables equality can be checked with `Objects::equal($a, $b)`. It supports null, primitive types, objects, and `ObjectInterface` implementations as well as it is expected.

### Comparing objects

Objects can be compared if they implement `precore\lang\Comparable` or if proper `precore\util\Comparator` is used which can compare the given objects. In several cases, comparing two objects
depend on their member variables so they need to be compared as well. It also can be simplified with `precore\util\ComparisonChain`.

```php
$strcmp = function ($left, $right) {
    return strcmp($left, $right);
};
$result = ComparisonChain::start()
    ->withClosure('aaa', 'aaa', $strcmp)
    ->withClosure('abc', 'bcd', $strcmp)
    ->withClosure('abc', 'bcd', $strcmp)
    ->result();
// $result < 0, because abc < bcd
```

In the previous example, the third comparison will not be executed, since it is unnecessary.

5. Preconditions
----------------

It is a very lightweight assertion tool, which supports argument, object state, null value, and array index checking. Customized messages can be passed with arguments similar to `printf()`.
 
```php
/**
 * @param $number
 * @throws \InvalidArgumentException if $number is 0
 */
function divide($number) {
    Preconditions::checkArgument($number != 0, 'Division by zero');
    return $this->value / Preconditions::checkNotNull($number, 'Argument cannot be null');
}
```

6. Stopwatch
------------

Useful for performance measurement, logging, and recognizing bottlenecks. Its `__toString()` method returns the elapsed time with the best abbreviate.

```php
$stopwatch = Stopwatch::createStarted();
// ... doing something
echo $stopwatch->elapsed(TimeUnit::$MILLISECONDS);
// ... doing something
echo $stopwatch;
$stopwatch->reset();
$stopwatch->start();
```

7. Profiler
-----------

### Basics

It helps the developer gather performance data. A profiler consists one or more stopwatches. Stopwatches are driven by statements in the source code.

```php
$profiler = new Profiler('p1');
$profiler->start('t1');
$profiler->start('t2');
$profiler->stop();
$profiler->printOut();
```

The output of the above program would something like this:

```
 + Profiler [p1]
 |-- elapsed time                           [t1]     1 ms.
 |-- elapsed time                           [t2]     2 ms.
 |-- Total                                  [p1]     3 ms.
```

It also supports nested profilers.

```php
$profiler = new Profiler('p1');
$profiler->start('t1');
$profiler->startNested('np1');

// this should be in another method
$nested = ProfilerRegistry::instance()->get('np1');
$nested->start('np1-t1');
$nested->stop();
// ----------------------

$profiler->start('t2');
$profiler->stop();
$profiler->printOut();
```

And the output:

```
 + Profiler [p1]
    + Profiler [np1]
    |-- elapsed time                       [np1-t1]     0 µs.
    |-- Subtotal                              [np1]  4.501 ms.
 |-- elapsed time                           [t1]  5.001 ms.
 |-- elapsed time                           [t2]     0 µs.
 |-- Total                                  [p1]  5.501 ms.
```

### AOP

With `precore`, you can use an annotation on methods for profiling them. For this feature, you need to load and configure
[Go! AOP PHP](https://github.com/lisachenko/go-aop-php). You have to register `ProfileLogAspect` in your `AspectKernel` implementation.
This aspect manages the necessary `Profiler` objects and the output will be logged via `lf4php`. If you run the unit tests of `precore`,
you will see the output of a test case.

```php
class ProfileFixture
{
    /**
     * @Profile(name="Main process")
     */
    public function main()
    {
        $this->foo1();
        $this->foo2();
    }

    /**
     * @Profile
     */
    protected function foo1()
    {
        $this->bar();
    }

    /**
     * @Profile
     */
    protected function bar()
    {
    }

    /**
     * @Profile(name="very fast method")
     */
    protected function foo2()
    {
    }
}

$fixture = new ProfileFixture();
$fixture->main();
```

In the log:

```
 + Profiler [Main process]
    + Profiler [foo1]
        + Profiler [bar]
        |-- elapsed time                         [exec]     0 µs.
        |-- Subtotal                              [bar]   26.5 ms.
    |-- elapsed time                         [exec]     27 ms.
    |-- Subtotal                             [foo1]     27 ms.
    + Profiler [very fast method]
    |-- elapsed time                         [exec]     0 µs.
    |-- Subtotal                 [very fast method]     0 µs.
 |-- elapsed time                         [exec]   30.5 ms.
 |-- Total                        [Main process]   30.5 ms.
```

8. Collections
--------------

Collection related static functions help you sorting objects. The comparing logic can be based on the `compareTo` method if objects implement `Comparable` interface,
or you can utilize a `Comparator`. Currently `ArrayObject` instances and `array`s can be sorted, and an `SplHeap` implementation can be created
for that. `StringComparator` enum contains the 4 basic string comparison algorithm provided by PHP.

Using a heap to sort strings in natural order:

```php
$heap = Collections::createHeap(Collections::reverseOrder(StringComparator::$NATURAL));
$heap->insert('rfc1.txt');
$heap->insert('rfc2086.txt');
$heap->insert('rfc822.txt');
foreach ($heap as $string) {
    echo $string . "\n";
}
```

The above program results the following output:

```
rfc1.txt
rfc822.txt
rfc2086.txt
```

A more complex example, where the given people should be sorted according to their name. If the name is the same, their age must be considered:

```php
final class Person implements Comparable
{
    private $name;
    private $age;

    public function __construct($name, $age)
    {
        $this->name = Preconditions::checkNotNull($name);
        $this->age = Preconditions::checkNotNull($age);
    }

    public function compareTo($object)
    {
        ObjectClass::forName(__CLASS__)->cast(Preconditions::checkNotNull($object));
        /* @var $object Person */
        return ComparisonChain::start()
            ->withComparator($this->name, $object->name, StringComparator::$NATURAL_CASE_INSENSITIVE)
            ->withClosure($this->age, $object->age, function ($age1, $age2) {
                return $age1 - $age2;
            })
            ->result();
    }

    public function __toString()
    {
        return Objects::toStringHelper($this)
            ->add($this->name)
            ->add($this->age)
            ->toString();
    }
}

$array = [
    new Person('John', 21),
    new Person('Johnny', 10),
    new Person('John', 70),
    new Person('Mary', 13)
];
Arrays::sort($array);
echo Joiner::on(', ')->join($array);
```

This program prints out the following:

```
Person{John, 21}, Person{John, 70}, Person{Johnny, 10}, Person{Mary, 13}
```
