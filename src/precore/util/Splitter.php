<?php

namespace precore\util;

use ArrayIterator;
use Iterator;
use RuntimeException;
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
    const UTF_8 = 'UTF-8';

    protected $omitEmptyStrings;
    protected $trimResults;

    /**
     * @param $delimiter
     * @return SimpleSplitter
     */
    public static function on($delimiter)
    {
        return new SimpleSplitter($delimiter, false, false);
    }

    /**
     * @param string $pattern
     * @return PatternSplitter
     */
    public static function onPattern($pattern)
    {
        return new PatternSplitter($pattern, false, false);
    }

    /**
     * @param int $length
     * @return FixedLengthSplitter
     */
    public static function fixedLength($length)
    {
        return new FixedLengthSplitter($length, false, false);
    }

    protected function __construct($trimResult, $omitEmptyStrings)
    {
        $this->trimResults = $trimResult;
        $this->omitEmptyStrings = $omitEmptyStrings;
    }

    /**
     * @return static
     */
    protected abstract function copy();

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
     * @return static
     */
    public final function trimResults()
    {
        $splitter = $this->copy();
        $splitter->omitEmptyStrings = $this->omitEmptyStrings;
        $splitter->trimResults = true;
        return $splitter;
    }

    /**
     * Returns a splitter that behaves equivalently to this splitter, but
     * automatically omits empty strings from the results. For example
     * <pre>
     *     Splitter::on(',')->omitEmptyStrings()->split(',a,,,b,c,,')
     * </pre>
     * returns a Traversable containing only ["a", "b", "c"].
     *
     * @return static
     */
    public final function omitEmptyStrings()
    {
        $splitter = $this->copy();
        $splitter->omitEmptyStrings = true;
        $splitter->trimResults = $this->trimResults;
        return $splitter;
    }

    /**
     * @param string $input
     * @return Traversable
     */
    public final function split($input)
    {
        Preconditions::checkArgument(is_string($input), 'input must be a string');
        return FluentIterable::from($this->rawSplitIterator($input))
            ->transform(
                function ($element) {
                    return $this->trimResults ? trim($element) : $element;
                }
            )
            ->filter(
                function ($element) {
                    return !$this->omitEmptyStrings || $element !== '';
                }
            );
    }
}

final class SimpleSplitter extends Splitter
{
    /**
     * @var string
     */
    private $delimiter;

    /**
     * @var bool
     */
    private $eager;

    /**
     * @param string $delimiter
     * @param $trimResult
     * @param $omitEmptyStrings
     * @param boolean $eager
     */
    public function __construct($delimiter, $trimResult, $omitEmptyStrings, $eager = false)
    {
        parent::__construct($trimResult, $omitEmptyStrings);
        Preconditions::checkArgument(is_string($delimiter), 'delimiter must be a string');
        $this->delimiter = $delimiter;
        $this->eager = $eager;
    }

    /**
     * @return SimpleSplitter
     */
    public function eager()
    {
        return new SimpleSplitter($this->delimiter, $this->trimResults, $this->omitEmptyStrings, true);
    }

    /**
     * @return Splitter
     */
    protected function copy()
    {
        return new SimpleSplitter($this->delimiter, $this->trimResults, $this->omitEmptyStrings, $this->eager);
    }

    /**
     * @param $input
     * @return Iterator
     */
    protected function rawSplitIterator($input)
    {
        return $this->eager
            ? new ArrayIterator(explode($this->delimiter, $input))
            : new SimpleSplitIterator($this->delimiter, $input);
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
     * @param $trimResult
     * @param $omitEmptyStrings
     */
    protected function __construct($pattern, $trimResult, $omitEmptyStrings)
    {
        parent::__construct($trimResult, $omitEmptyStrings);
        Preconditions::checkArgument(is_string($pattern), 'pattern must be a string');
        $this->pattern = $pattern;
    }

    /**
     * @return Splitter
     */
    protected function copy()
    {
        return new PatternSplitter($this->pattern, $this->trimResults, $this->omitEmptyStrings);
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
     * @param $trimResult
     * @param $omitEmptyStrings
     */
    protected function __construct($length, $trimResult, $omitEmptyStrings)
    {
        parent::__construct($trimResult, $omitEmptyStrings);
        Preconditions::checkArgument(is_int($length) && 0 < $length, 'length must be a positive integer');
        $this->length = $length;
    }


    /**
     * @return Splitter
     */
    protected function copy()
    {
        return new FixedLengthSplitter($this->length, $this->trimResults, $this->omitEmptyStrings);
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

final class SimpleSplitIterator implements Iterator
{
    private $delimiter;
    private $delimiterLength;
    private $input;
    private $end = false;
    private $current;
    private $started = false;

    public function __construct($delimiter, $input)
    {
        $this->delimiter = $delimiter;
        $this->delimiterLength = mb_strlen($delimiter, Splitter::UTF_8);
        $this->input = $input;
    }

    public function current()
    {
        return $this->current;
    }

    public function next()
    {
        if (!$this->end) {
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
        if (!$this->started) {
            $this->started = true;
            $this->calculateCurrent();
        } else {
            throw new RuntimeException('this iterator cannot be rewound');
        }
    }

    private function calculateCurrent()
    {
        $delimiterPos = mb_strpos($this->input, $this->delimiter, null, Splitter::UTF_8);
        if ($delimiterPos === false) {
            $this->end = true;
            $this->current = $this->input;
        } else {
            $this->current = mb_substr($this->input, 0, $delimiterPos, Splitter::UTF_8);
            $this->input = mb_substr($this->input, $delimiterPos + $this->delimiterLength, null, Splitter::UTF_8);
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
        $this->fullLength = mb_strlen($input, Splitter::UTF_8);
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
        $this->calculateCurrent();
    }

    public function key()
    {
        return null;
    }

    public function valid()
    {
        return $this->pos < $this->fullLength;
    }

    public function rewind()
    {
        $this->pos = 0;
        $this->calculateCurrent();
    }

    private function calculateCurrent()
    {
        $this->current = mb_substr($this->input, $this->pos, $this->limitLength, Splitter::UTF_8);
    }
}
