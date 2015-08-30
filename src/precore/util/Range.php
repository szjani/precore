<?php
/*
 * Copyright (c) 2012-2015 Janos Szurovecz
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in
 * the Software without restriction, including without limitation the rights to
 * use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies
 * of the Software, and to permit persons to whom the Software is furnished to do
 * so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace precore\util;

use BadMethodCallException;
use precore\lang\ClassCastException;
use precore\lang\Comparable;
use precore\lang\NullPointerException;
use precore\lang\Object;
use precore\lang\ObjectInterface;
use Traversable;

/**
 * A range is an interval, defined by two endpoints.
 * Ranges may "extend to infinity" -- for example, the range "x > 3" contains arbitrarily large values
 * -- or may be finitely constrained, for example "2 <= x < 5".
 *
 * <p>The endpoints and the values passed to query methods must be able to be compared.
 * This comparison can be explicitly set, but Range supports natural ordering on the following types:
 * <ul>
 *   <li>strings (strcmp)</li>
 *   <li>numbers</li>
 *   <li>DateTime</li>
 *   <li>boolean</li>
 * </ul>
 * It also supports objects that implement Comparable interface, like the Enum.
 * </p>
 *
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class Range extends Object
{
    private static $ALL;

    /**
     * @var Cut
     */
    private $lowerBound;

    /**
     * @var Cut
     */
    private $upperBound;

    private function __construct(Cut $lowerBound, Cut $upperBound)
    {
        Preconditions::checkArgument(
            $lowerBound->compareTo($upperBound) <= 0
            && $lowerBound !== Cut::aboveAll()
            && $upperBound !== Cut::belowAll(),
            'Invalid range: %s', self::toStringBounds($lowerBound, $upperBound)
        );
        $this->lowerBound = $lowerBound;
        $this->upperBound = $upperBound;
    }

    private static function toStringBounds(Cut $lower, Cut $upper)
    {
        return $lower->describeAsLowerBound() . '..' . $upper->describeAsUpperBound();
    }

    private static function comparatorOrNatural($endpoint, Comparator $comparator = null)
    {
        $result = Ordering::natural();
        if ($comparator !== null) {
            $result = $comparator;
        } elseif (is_string($endpoint)) {
            $result = Ordering::usingToString();
        } elseif (is_numeric($endpoint)) {
            $result = Numbers::naturalOrdering();
        } elseif (is_bool($endpoint)) {
            $result = Booleans::naturalOrdering();
        } elseif ($endpoint instanceof \DateTime) {
            $result = DateTimes::naturalOrdering();
        }
        return $result;
    }

    public static function init()
    {
        self::$ALL = new Range(Cut::belowAll(), Cut::aboveAll());
    }

    /**
     * @param $lower
     * @param $upper
     * @param Comparator $comparator
     * @return Range
     */
    public static function open($lower, $upper, Comparator $comparator = null)
    {
        $comparator = self::comparatorOrNatural($lower, $comparator);
        return new Range(Cut::aboveValue($lower, $comparator), Cut::belowValue($upper, $comparator));
    }

    /**
     * @param $lower
     * @param $upper
     * @param Comparator $comparator
     * @return Range
     */
    public static function closed($lower, $upper, Comparator $comparator = null)
    {
        $comparator = self::comparatorOrNatural($lower, $comparator);
        return new Range(Cut::belowValue($lower, $comparator), Cut::aboveValue($upper, $comparator));
    }

    /**
     * @param $lower
     * @param $upper
     * @param Comparator $comparator
     * @return Range
     */
    public static function openClosed($lower, $upper, Comparator $comparator = null)
    {
        $comparator = self::comparatorOrNatural($lower, $comparator);
        return new Range(Cut::aboveValue($lower, $comparator), Cut::aboveValue($upper, $comparator));
    }

    /**
     * @param $lower
     * @param $upper
     * @param Comparator $comparator
     * @return Range
     */
    public static function closedOpen($lower, $upper, Comparator $comparator = null)
    {
        $comparator = self::comparatorOrNatural($lower, $comparator);
        return new Range(Cut::belowValue($lower, $comparator), Cut::belowValue($upper, $comparator));
    }

    /**
     * @return Range
     */
    public static function all()
    {
        return self::$ALL;
    }

    /**
     * @param $endpoint
     * @param Comparator $comparator
     * @return Range
     */
    public static function greaterThan($endpoint, Comparator $comparator = null)
    {
        $comparator = self::comparatorOrNatural($endpoint, $comparator);
        return new Range(Cut::aboveValue($endpoint, $comparator), Cut::aboveAll());
    }

    /**
     * @param $endpoint
     * @param Comparator $comparator
     * @return Range
     */
    public static function lessThan($endpoint, Comparator $comparator = null)
    {
        $comparator = self::comparatorOrNatural($endpoint, $comparator);
        return new Range(Cut::belowAll(), Cut::belowValue($endpoint, $comparator));
    }

    /**
     * @param $endpoint
     * @param Comparator $comparator
     * @return Range
     */
    public static function atLeast($endpoint, Comparator $comparator = null)
    {
        $comparator = self::comparatorOrNatural($endpoint, $comparator);
        return new Range(Cut::belowValue($endpoint, $comparator), Cut::aboveAll());
    }

    /**
     * @param $endpoint
     * @param Comparator $comparator
     * @return Range
     */
    public static function atMost($endpoint, Comparator $comparator = null)
    {
        $comparator = self::comparatorOrNatural($endpoint, $comparator);
        return new Range(Cut::belowAll(), Cut::aboveValue($endpoint, $comparator));
    }

    public function __invoke($element)
    {
        return $this->contains($element);
    }

    /**
     * @param $value
     * @return bool
     * @throws NullPointerException if $value is null
     */
    public function contains($value)
    {
        Preconditions::checkNotNull($value);
        return $this->lowerBound->isLessThan($value) && !$this->upperBound->isLessThan($value);
    }

    /**
     * @param Traversable $elements
     * @return bool
     */
    public function containsAll(Traversable $elements)
    {
        return Iterators::all(Iterators::from($elements), $this);
    }

    public function isEmpty()
    {
        return $this->lowerBound->equals($this->upperBound);
    }

    public function lowerEndpoint()
    {
        return $this->lowerBound->endpoint();
    }

    public function upperEndpoint()
    {
        return $this->upperBound->endpoint();
    }

    /**
     * @param Range $other
     * @return boolean
     */
    public function encloses(Range $other)
    {
        return $this->lowerBound->compareTo($other->lowerBound) <= 0
            && $this->upperBound->compareTo($other->upperBound) >= 0;
    }

    public function isConnected(Range $other)
    {
        return $this->lowerBound->compareTo($other->upperBound) <= 0
            && $other->lowerBound->compareTo($this->upperBound) <= 0;
    }

    /**
     * @param Range $connectedRange
     * @return Range
     */
    public function intersection(Range $connectedRange)
    {
        $lowerCmp = $this->lowerBound->compareTo($connectedRange->lowerBound);
        $upperCmp = $this->upperBound->compareTo($connectedRange->upperBound);
        if ($lowerCmp >= 0 && $upperCmp <= 0) {
            return $this;
        } elseif ($lowerCmp <= 0 && $upperCmp >= 0) {
            return $connectedRange;
        } else {
            $newLower = ($lowerCmp >= 0) ? $this->lowerBound : $connectedRange->lowerBound;
            $newUpper = ($upperCmp <= 0) ? $this->upperBound : $connectedRange->upperBound;
            return new Range($newLower, $newUpper);
        }
    }

    /**
     * @param Range $other
     * @return Range
     */
    public function span(Range $other)
    {
        $lowerCmp = $this->lowerBound->compareTo($other->lowerBound);
        $upperCmp = $this->upperBound->compareTo($other->upperBound);
        if ($lowerCmp <= 0 && $upperCmp >= 0) {
            return $this;
        } elseif ($lowerCmp >= 0 && $upperCmp <= 0) {
            return $other;
        } else {
            $newLower = ($lowerCmp <= 0) ? $this->lowerBound : $other->lowerBound;
            $newUpper = ($upperCmp >= 0) ? $this->upperBound : $other->upperBound;
            return new Range($newLower, $newUpper);
        }
    }

    public function equals(ObjectInterface $object = null)
    {
        return $object instanceof Range
            && $this->lowerBound->equals($object->lowerBound)
            && $this->upperBound->equals($object->upperBound);
    }

    public function toString()
    {
        return self::toStringBounds($this->lowerBound, $this->upperBound);
    }
}

/**
 * It is not intended to be used in your code.
 *
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
abstract class Cut extends Object implements Comparable
{
    private $endpoint;

    /**
     * @var Comparator
     */
    private $comparator;

    public function __construct($endpoint, Comparator $comparator = null)
    {
        $this->endpoint = $endpoint;
        $this->comparator = $comparator;
    }

    /**
     * @return string
     */
    public abstract function describeAsLowerBound();

    /**
     * @return string
     */
    public abstract function describeAsUpperBound();

    /**
     * @return Cut
     */
    public static function belowAll()
    {
        return BelowAll::instance();
    }

    /**
     * @return Cut
     */
    public static function aboveAll()
    {
        return AboveAll::instance();
    }

    /**
     * @param $value
     * @param Comparator $comparator
     * @return Cut
     */
    public static function belowValue($value, Comparator $comparator)
    {
        return new BelowValue($value, $comparator);
    }

    /**
     * @param $value
     * @param Comparator $comparator
     * @return Cut
     */
    public static function aboveValue($value, Comparator $comparator)
    {
        return new AboveValue($value, $comparator);
    }

    public abstract function isLessThan($value);

    public function endpoint()
    {
        return $this->endpoint;
    }

    /**
     * @return Comparator
     */
    protected function comparator()
    {
        return $this->comparator;
    }

    /**
     * @param $object
     * @return int a negative integer, zero, or a positive integer
     *         as this object is less than, equal to, or greater than the specified object.
     * @throws ClassCastException - if the specified object's type prevents it from being compared to this object.
     * @throws NullPointerException if the specified object is null
     */
    public function compareTo($object)
    {
        Cut::objectClass()->cast(Preconditions::checkNotNull($object));
        /* @var $object Cut */
        if ($object === self::belowAll()) {
            return 1;
        }
        if ($object === self::aboveAll()) {
            return -1;
        }
        $result = $this->comparator->compare($this->endpoint, $object->endpoint);
        if ($result !== 0) {
            return $result;
        }
        return Booleans::compare($this instanceof AboveValue, $object instanceof AboveValue);
    }

    public function equals(ObjectInterface $object = null)
    {
        return $object !== null
            && $this->getClassName() === $object->getClassName()
            && $this->compareTo($object) === 0;
    }
}

/**
 * It is not intended to be used in your code.
 *
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class BelowAll extends Cut
{
    private static $INSTANCE;

    public static function init()
    {
        self::$INSTANCE = new BelowAll(null, null);
    }

    public static function instance()
    {
        return self::$INSTANCE;
    }

    public function isLessThan($value)
    {
        return true;
    }

    public function compareTo($object)
    {
        return $object === $this ? 0 : -1;
    }

    public function toString()
    {
        return '-?';
    }

    /**
     * @return string
     */
    public function describeAsLowerBound()
    {
        return '(' . $this->toString();
    }

    /**
     * @return string
     */
    public function describeAsUpperBound()
    {
        throw new BadMethodCallException();
    }
}

final class BelowValue extends Cut
{
    public function __construct($endpoint, Comparator $comparator = null)
    {
        parent::__construct(Preconditions::checkNotNull($endpoint), Preconditions::checkNotNull($comparator));
    }

    public function isLessThan($value)
    {
        return $this->comparator()->compare($this->endpoint(), $value) <= 0;
    }

    public function toString()
    {
        return ToStringHelper::valueToString($this->endpoint());
    }

    /**
     * @return string
     */
    public function describeAsLowerBound()
    {
        return '[' . $this->toString();
    }

    /**
     * @return string
     */
    public function describeAsUpperBound()
    {
        return $this->toString() . ')';
    }
}

/**
 * It is not intended to be used in your code.
 *
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class AboveAll extends Cut
{
    private static $INSTANCE;

    public static function init()
    {
        self::$INSTANCE = new AboveAll(null, null);
    }

    public static function instance()
    {
        return self::$INSTANCE;
    }

    public function isLessThan($value)
    {
        return false;
    }

    public function compareTo($object)
    {
        return $object === $this ? 0 : 1;
    }

    public function toString()
    {
        return '?';
    }

    /**
     * @return string
     */
    public function describeAsLowerBound()
    {
        throw new BadMethodCallException();
    }

    /**
     * @return string
     */
    public function describeAsUpperBound()
    {
        return $this->toString() . ')';
    }
}

/**
 * It is not intended to be used in your code.
 *
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class AboveValue extends Cut
{
    public function __construct($endpoint, Comparator $comparator = null)
    {
        parent::__construct(Preconditions::checkNotNull($endpoint), Preconditions::checkNotNull($comparator));
    }

    public function isLessThan($value)
    {
        return $this->comparator()->compare($this->endpoint(), $value) < 0;
    }

    public function toString()
    {
        return ToStringHelper::valueToString($this->endpoint());
    }

    /**
     * @return string
     */
    public function describeAsLowerBound()
    {
        return '(' . $this->toString();
    }

    /**
     * @return string
     */
    public function describeAsUpperBound()
    {
        return $this->toString() . ']';
    }
}
BelowAll::init();
AboveAll::init();
Range::init();
