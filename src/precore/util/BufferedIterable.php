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

use ArrayIterator;
use Iterator;
use IteratorAggregate;

/**
 * An {@link IteratorAggregate} which provides the ability to iterate over dynamically loaded data.
 *
 * <p>
 * The used {@link ChunkProvider}s responsibility is to load and return the proper chunk of data
 * according to the given offset. The number of the expected items can be limited and the result can be filtered.
 * It is handy when the result is huge, because only the current chunk need to be stored in the memory.
 * </p>
 *
 * <p>
 * The data loading will be stopped in the following cases:
 * <ul>
 *   <li>The {@link ChunkProvider} returns empty data</li>
 *   <li>A limit is defined and reached</li>
 *   <li>The chunk provider call limit reached</li>
 * </ul>
 * </p>
 *
 * <p>Note that the provider call limit is 1 by default to avoid infinite loop. Always use a reasonable number!</p>
 *
 * <pre>
 *   $userProvider = function ($offset) {
 *       return $userRepository->get($offset, 10);
 *   }
 *   $adminFilter = function ($user) {
 *       return $user->isAdmin();
 *   }
 *   $top100AdminUsers = BufferedIterable::withChunkFunction($userProvider)
 *       ->filter($adminFilter)
 *       ->providerCallLimit(40)
 *       ->limit(100);
 *   foreach ($top100AdminUsers as $admin) {
 *       // do something
 *   }
 * </pre>
 * In the above example we always load 10 users through a repository until we get 100 admin users.
 *
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class BufferedIterable implements IteratorAggregate
{
    /**
     * @var callable
     */
    private $filter;

    /**
     * @var ChunkProvider
     */
    private $chunkProvider;

    /**
     * @var int
     */
    private $limit;

    /**
     * @var int
     */
    private $providerCallLimit;

    private function __construct(
        ChunkProvider $chunkProvider,
        callable $filter = null,
        $limit = PHP_INT_MAX,
        $providerCallLimit = 1
    ) {
        $this->chunkProvider = $chunkProvider;
        if ($filter === null) {
            $filter = Predicates::alwaysTrue();
        }
        $this->filter = $filter;
        $this->limit = $limit;
        $this->providerCallLimit = $providerCallLimit;
    }

    /**
     * Creates a {@link BufferedIterable} with the given {@link ChunkProvider}.
     *
     * @param ChunkProvider $chunkProvider
     * @return BufferedIterable
     */
    public static function withChunkProvider(ChunkProvider $chunkProvider)
    {
        return new BufferedIterable($chunkProvider);
    }

    /**
     * Creates a {@link BufferedIterable} with the given function as chunk provider.
     * The function will get the offset as parameter.
     *
     * @param callable $function
     * @return BufferedIterable
     */
    public static function withChunkFunction(callable $function)
    {
        return self::withChunkProvider(new FunctionalChunkProvider($function));
    }

    /**
     * Limits the size of the result.
     *
     * @param int $limit
     * @return BufferedIterable
     * @throws \InvalidArgumentException if $limit is not a number or <= 0
     */
    public function limit($limit)
    {
        Preconditions::checkArgument(is_int($limit) && 0 < $limit, 'Limit must be a positive integer!');
        return new BufferedIterable($this->chunkProvider, $this->filter, $limit, $this->providerCallLimit);
    }

    /**
     * Limits the number of chunk provider calls. The default value is 1.
     *
     * @param $limit
     * @return BufferedIterable
     */
    public function providerCallLimit($limit)
    {
        return new BufferedIterable($this->chunkProvider, $this->filter, $this->limit, $limit);
    }

    /**
     * Filters the result list with the given predicate.
     *
     * @param $predicate
     * @return BufferedIterable
     */
    public function filter($predicate)
    {
        return new BufferedIterable($this->chunkProvider, $predicate, $this->limit, $this->providerCallLimit);
    }

    /**
     * @return Iterator
     */
    public function getIterator()
    {
        return new BufferedIterator($this->chunkProvider, $this->filter, $this->limit, $this->providerCallLimit);
    }
}

/**
 * It is not intended to be used in your code.
 *
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class BufferedIterator implements Iterator
{
    /**
     * @var ChunkProvider final
     */
    private $chunkProvider;

    /**
     * @var callable final
     */
    private $filter;

    /**
     * @var int final
     */
    private $limit;

    /**
     * @var int final
     */
    private $providerCallLimit;

    /**
     * @var Iterator
     */
    private $currentChunk;

    /**
     * @var int
     */
    private $remaining;

    private $providerCalls = 0;

    private $offset = 0;

    /**
     * BufferedIterator constructor.
     * @param ChunkProvider $chunkProvider
     * @param callable $filter
     * @param int $limit
     * @param $providerCallLimit
     */
    public function __construct(ChunkProvider $chunkProvider, callable $filter, $limit, $providerCallLimit)
    {
        $this->chunkProvider = $chunkProvider;
        $this->filter = $filter;
        $this->limit = $limit;
        $this->remaining = $limit;
        $this->providerCallLimit = $providerCallLimit;
    }

    public function current()
    {
        return $this->currentChunk->current();
    }

    public function next()
    {
        $this->currentChunk->next();
    }

    public function key()
    {
        $this->currentChunk->key();
    }

    public function valid()
    {
        if ($this->providerCalls < $this->providerCallLimit
            && 0 < $this->remaining
            && ($this->currentChunk === null || !$this->currentChunk->valid())) {

            $this->callNext();
        }
        return $this->currentChunk->valid();
    }

    public function rewind()
    {
        $this->offset = 0;
        $this->remaining = $this->limit;
        $this->providerCalls = 0;
        $this->callNext();
    }

    private function callNext()
    {
        $chunk = $this->chunkProvider->getChunk($this->offset);
        $filteredChunk = FluentIterable::from($chunk)
            ->filter($this->filter)
            ->limit($this->remaining)
            ->toArray();
        $this->currentChunk = new ArrayIterator($filteredChunk);
        $chunkSize = count($filteredChunk);
        $this->offset += $chunkSize;
        $this->remaining -= $chunkSize;
        $this->providerCalls++;
    }
}

/**
 * It is not intended to be used in your code.
 *
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class FunctionalChunkProvider implements ChunkProvider
{
    /**
     * @var callable
     */
    private $function;

    /**
     * @param callable $function
     */
    public function __construct(callable $function)
    {
        $this->function = $function;
    }

    /**
     * @param $offset
     * @return Iterator
     */
    public function getChunk($offset)
    {
        return call_user_func($this->function, $offset);
    }
}