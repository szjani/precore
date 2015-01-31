<?php

namespace precore\util;

use ArrayIterator;
use FilterIterator;
use Iterator;
use Traversable;

/**
 * An object that divides strings into substrings, by recognizing a <i>separator</i> (a.k.a. "delimiter")
 * which can be expressed as a string, regular expression, or by using a fixed substring length.
 * This class provides the complementary functionality to {@link Joiner}.
 *
 * <p>Here is the most basic example of {@link Splitter} usage:
 * <pre>
 *   Splitter::on(',').split('foo,bar')
 * </pre>
 *
 * This invocation returns an {@link \Traversable} containing "foo" and "bar", in that order.
 *
 * <p>By default {@link Splitter}'s behavior is very simplistic:
 * <pre>
 *   Splitter::on(',').split('foo,,bar, quux')
 * </pre>
 *
 * This returns a {@link \Traversable} containing ["foo", "", "bar", " quux"].
 *
 * Notice that the splitter does not assume that you want empty strings removed,
 * or that you wish to trim whitespace. If you want features like these, simply
 * ask for them:
 * <pre>
 *   $splitter = Splitter::on(',')
 *       ->trimResults()
 *       ->omitEmptyStrings();
 * </pre>
 *
 * Now $splitter.split("foo, ,bar, quux,") returns a {@link \Traversable}
 * containing just ["foo", "bar", "quux"]. Note that the order in which
 * the configuration methods are called is never significant; for instance,
 * trimming is always applied first before checking for an empty result,
 * regardless of the order in which the {@link Splitter::trimResults()} and
 * {@link Splitter::omitEmptyStrings()} methods were invoked.
 *
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
abstract class Splitter
{
    /**
     * @var callable
     */
    private $converter;

    /**
     * @param $delimiter
     * @return Splitter
     */
    public static function on($delimiter)
    {
        return new SimpleSplitter($delimiter);
    }

    /**
     * @param string $pattern
     * @return Splitter
     */
    public static function onPattern($pattern)
    {
        return new PatternSplitter($pattern);
    }

    /**
     * @param int $length
     * @return Splitter
     */
    public static function fixedLength($length)
    {
        return new FixedLengthSplitter($length);
    }

    /**
     * @param callable $converter
     */
    protected function __construct(callable $converter = null)
    {
        if ($converter === null) {
            $converter = function ($element) {
                return $element;
            };
        }
        $this->converter = $converter;
    }

    /**
     * @param callable $newConverter
     * @return Splitter
     */
    protected abstract function copy(callable $newConverter);

    /**
     * @param $input
     * @return Iterator
     */
    protected abstract function rawSplitIterator($input);

    /**
     * Returns a splitter that behaves equivalently to this splitter, but
     * automatically removes leading and trailing whitespace characters
     * from each returned substring. For example
     * <pre>
     *     Splitter::on(',')->trimResults()->split(' a, b ,c ')
     * </pre>
     * returns a Traversable containing ["a", "b", "c"].
     *
     * @return Splitter
     */
    public final function trimResults()
    {
        return $this->copy(
            function ($element) {
                $element = $element === null ? null : trim($element);
                return call_user_func($this->converter, $element);
            }
        );
    }

    /**
     * Returns a splitter that behaves equivalently to this splitter, but
     * automatically omits empty strings from the results. For example
     * <pre>
     *     Splitter::on(',')->omitEmptyStrings()->split(',a,,,b,c,,')
     * </pre>
     * returns a Traversable containing only ["a", "b", "c"].
     *
     * @return Splitter
     */
    public final function omitEmptyStrings()
    {
        return $this->copy(
            function ($element) {
                $element = call_user_func($this->converter, $element);
                return $element === '' ? null : $element;
            }
        );
    }

    /**
     * @param string $input
     * @return Traversable
     */
    public final function split($input)
    {
        Preconditions::checkArgument(is_string($input), 'input must be a string');
        return new ConverterFilterIterator($this->rawSplitIterator($input), $this->converter);
    }
}

final class SimpleSplitter extends Splitter
{
    /**
     * @var string
     */
    private $delimiter;

    /**
     * @param $delimiter
     * @param callable $converter
     */
    public function __construct($delimiter, callable $converter = null)
    {
        parent::__construct($converter);
        Preconditions::checkArgument(is_string($delimiter), 'delimiter must be a string');
        $this->delimiter = $delimiter;
    }

    /**
     * @param callable $newConverter
     * @return Splitter
     */
    protected function copy(callable $newConverter)
    {
        return new SimpleSplitter($this->delimiter, $newConverter);
    }

    /**
     * @param $input
     * @return Iterator
     */
    protected function rawSplitIterator($input)
    {
        return new SimpleSplitIterator($this->delimiter, $input);
    }
}

final class PatternSplitter extends Splitter
{
    /**
     * @var string
     */
    private $pattern;

    /**
     * @param string $pattern
     * @param callable $converter
     */
    protected function __construct($pattern, callable $converter = null)
    {
        parent::__construct($converter);
        Preconditions::checkArgument(is_string($pattern), 'pattern must be a string');
        $this->pattern = $pattern;
    }

    /**
     * @param callable $newConverter
     * @return Splitter
     */
    protected function copy(callable $newConverter)
    {
        return new PatternSplitter($this->pattern, $newConverter);
    }

    /**
     * @param $input
     * @return Iterator
     */
    protected function rawSplitIterator($input)
    {
        return new ArrayIterator(preg_split($this->pattern, $input));
    }
}

final class FixedLengthSplitter extends Splitter
{
    /**
     * @var int
     */
    private $length;

    /**
     * @param int $length
     * @param callable $converter
     */
    protected function __construct($length, callable $converter = null)
    {
        parent::__construct($converter);
        Preconditions::checkArgument(is_int($length) && 0 < $length, 'length must be a positive integer');
        $this->length = $length;
    }


    /**
     * @param callable $newConverter
     * @return Splitter
     */
    protected function copy(callable $newConverter)
    {
        return new FixedLengthSplitter($this->length, $newConverter);
    }

    /**
     * @param $input
     * @return Iterator
     */
    protected function rawSplitIterator($input)
    {
        return new FixedLengthSplitIterator($this->length, $input);
    }
}

final class ConverterFilterIterator extends FilterIterator
{
    /**
     * @var callable
     */
    private $converter;

    /**
     * @param Iterator $iterator
     * @param callable $converter
     */
    public function __construct(Iterator $iterator, callable $converter)
    {
        parent::__construct($iterator);
        $this->converter = $converter;
    }

    public function accept()
    {
        return $this->current() !== null;
    }

    public function current()
    {
        return call_user_func($this->converter, parent::current());
    }
}

final class SimpleSplitIterator implements Iterator
{
    private $origInput;
    private $origInputLength;
    private $delimiter;
    private $delimiterLength;
    private $input;
    private $length = 0;
    private $current;

    public function __construct($delimiter, $input)
    {
        $this->delimiter = $delimiter;
        $this->delimiterLength = mb_strlen($delimiter);
        $this->input = $input;
        $this->origInput = $input;
        $this->origInputLength = mb_strlen($input);
    }

    public function current()
    {
        return $this->current;
    }

    public function next()
    {
        if ($this->length !== null) {
            $this->input = mb_substr($this->input, $this->length + $this->delimiterLength);
            $this->calculateCurrent();
        } else {
            $this->current = null;
        }
    }

    public function key()
    {
        return null;
    }

    public function valid()
    {
        return $this->current !== null;
    }

    public function rewind()
    {
        $this->input = $this->origInput;
        $this->length = 0;
        $this->calculateCurrent();
    }

    private function calculateCurrent()
    {
        $this->length = mb_strpos($this->input, $this->delimiter);
        if ($this->length === false) {
            $this->length = null;
            $this->current = $this->input;
        } else {
            $this->current = mb_substr($this->input, 0, $this->length);
        }
    }
}

final class FixedLengthSplitIterator implements Iterator
{
    private $fullLength;
    private $limitLength;
    private $input;
    private $current;
    private $pos = 0;

    public function __construct($limitLength, $input)
    {
        $this->fullLength = mb_strlen($input);
        $this->limitLength = $limitLength;
        $this->input = $input;
    }

    public function current()
    {
        return $this->current;
    }

    public function next()
    {
        $this->pos += $this->limitLength;
    }

    public function key()
    {
        return null;
    }

    public function valid()
    {
        $this->current = mb_substr($this->input, $this->pos, $this->limitLength);
        return $this->pos < $this->fullLength;
    }

    public function rewind()
    {
        $this->pos = 0;
    }
}
