<?php
declare(strict_types=1);

namespace precore\util;

use ArrayIterator;
use Iterator;
use precore\lang\BaseObject;
use precore\lang\ObjectInterface;
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
abstract class Splitter extends BaseObject
{
    const UTF_8 = 'UTF-8';

    protected $omitEmptyStrings;
    protected $trimResults;

    /**
     * @param $delimiter
     * @return SimpleSplitter
     */
    public static function on(string $delimiter) : SimpleSplitter
    {
        return new SimpleSplitter($delimiter, false, false);
    }

    /**
     * @param string $pattern
     * @return Splitter
     */
    public static function onPattern(string $pattern) : Splitter
    {
        return new PatternSplitter($pattern, false, false);
    }

    /**
     * @param int $length
     * @return Splitter
     */
    public static function fixedLength(int $length) : Splitter
    {
        return new FixedLengthSplitter($length, false, false);
    }

    protected function __construct(bool $trimResult, bool $omitEmptyStrings)
    {
        $this->trimResults = $trimResult;
        $this->omitEmptyStrings = $omitEmptyStrings;
    }

    /**
     * @return static
     */
    protected abstract function copy() : Splitter;

    /**
     * @param $input
     * @return Iterator
     */
    protected abstract function rawSplitIterator(string $input) : Iterator;

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
    public final function trimResults() : Splitter
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
    public final function omitEmptyStrings() : Splitter
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
    public final function split(string $input) : Traversable
    {
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

    public function equals(ObjectInterface $object = null) : bool
    {
        /* @var $object Splitter */
        return $object !== null
            && $this->getClassName() === $object->getClassName()
            && $this->omitEmptyStrings === $object->omitEmptyStrings
            && $this->trimResults === $object->trimResults;
    }


    public function toString() : string
    {
        return Objects::toStringHelper($this)
            ->add('trimResults', $this->trimResults)
            ->add('omitEmptyStrings', $this->omitEmptyStrings)
            ->toString();
    }


}

/**
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
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
     * @throws \InvalidArgumentException if $delimiter is not a string
     */
    public function __construct(string $delimiter, bool $trimResult, bool $omitEmptyStrings, $eager = false)
    {
        parent::__construct($trimResult, $omitEmptyStrings);
        $this->delimiter = $delimiter;
        $this->eager = $eager;
    }

    /**
     * @return SimpleSplitter
     */
    public function eager() : SimpleSplitter
    {
        return new SimpleSplitter($this->delimiter, $this->trimResults, $this->omitEmptyStrings, true);
    }

    /**
     * @return Splitter
     */
    protected function copy() : Splitter
    {
        return new SimpleSplitter($this->delimiter, $this->trimResults, $this->omitEmptyStrings, $this->eager);
    }

    /**
     * @param $input
     * @return Iterator
     */
    protected function rawSplitIterator(string $input) : Iterator
    {
        return $this->eager
            ? new ArrayIterator(explode($this->delimiter, $input))
            : new SimpleSplitIterator($this->delimiter, $input);
    }

    public function equals(ObjectInterface $object = null) : bool
    {
        /* @var $object SimpleSplitter */
        return parent::equals($object)
            && $this->eager === $object->eager
            && $this->delimiter === $object->delimiter;
    }

    public function toString() : string
    {
        return Objects::toStringHelper($this)
            ->add('parent', parent::toString())
            ->add('delimiter', $this->delimiter)
            ->add('eager', $this->eager)
            ->toString();
    }
}

/**
 * It is not intended to be used in your code.
 *
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
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
     * @throws \InvalidArgumentException if $pattern is not a string
     */
    public function __construct(string $pattern, bool $trimResult, bool $omitEmptyStrings)
    {
        parent::__construct($trimResult, $omitEmptyStrings);
        $this->pattern = $pattern;
    }

    /**
     * @return Splitter
     */
    protected function copy() : Splitter
    {
        return new PatternSplitter($this->pattern, $this->trimResults, $this->omitEmptyStrings);
    }

    /**
     * @param $input
     * @return Iterator
     */
    protected function rawSplitIterator(string $input) : Iterator
    {
        return new ArrayIterator(preg_split($this->pattern, $input));
    }

    public function equals(ObjectInterface $object = null) : bool
    {
        /* @var $object PatternSplitter */
        return parent::equals($object)
            && $this->pattern === $object->pattern;
    }

    public function toString() : string
    {
        return Objects::toStringHelper($this)
            ->add('parent', parent::toString())
            ->add('pattern', $this->pattern)
            ->toString();
    }
}

/**
 * It is not intended to be used in your code.
 *
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
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
     * @throws \InvalidArgumentException if $length is not a positive integer
     */
    public function __construct(int $length, bool $trimResult, bool $omitEmptyStrings)
    {
        parent::__construct($trimResult, $omitEmptyStrings);
        Preconditions::checkArgument(0 < $length, 'length must be a positive integer');
        $this->length = $length;
    }


    /**
     * @return Splitter
     */
    protected function copy() : Splitter
    {
        return new FixedLengthSplitter($this->length, $this->trimResults, $this->omitEmptyStrings);
    }

    /**
     * @param $input
     * @return Iterator
     */
    protected function rawSplitIterator(string $input) : Iterator
    {
        return new FixedLengthSplitIterator($this->length, $input);
    }

    public function equals(ObjectInterface $object = null) : bool
    {
        /* @var $object FixedLengthSplitter */
        return parent::equals($object)
            && $this->length === $object->length;
    }

    public function toString() : string
    {
        return Objects::toStringHelper($this)
            ->add('parent', parent::toString())
            ->add('length', $this->length)
            ->toString();
    }
}

/**
 * It is not intended to be used in your code.
 *
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class SimpleSplitIterator implements Iterator
{
    private $delimiter;
    private $delimiterLength;
    private $input;
    private $end = false;
    private $current;
    private $started = false;

    public function __construct(string $delimiter, string $input)
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

    private function calculateCurrent() : void
    {
        $delimiterPos = mb_strpos($this->input, $this->delimiter, 0, Splitter::UTF_8);
        if ($delimiterPos === false) {
            $this->end = true;
            $this->current = $this->input;
        } else {
            $this->current = mb_substr($this->input, 0, $delimiterPos, Splitter::UTF_8);
            $this->input = mb_substr($this->input, $delimiterPos + $this->delimiterLength, null, Splitter::UTF_8);
        }
    }
}

/**
 * It is not intended to be used in your code.
 *
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class FixedLengthSplitIterator implements Iterator
{
    private $fullLength;
    private $limitLength;
    private $input;
    private $current;
    private $pos = 0;

    public function __construct(int $limitLength, string $input)
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
