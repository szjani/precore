<?php
declare(strict_types=1);

namespace precore\util;

use ArrayObject;
use PHPUnit\Framework\TestCase;
use precore\lang\IllegalStateException;

/**
 * Class ExceptionsTest
 *
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class ExceptionsTest extends TestCase
{
    /**
     * @test
     */
    public function shouldReturnCausalChain()
    {
        $e1 = new \RuntimeException();
        $e2 = new \InvalidArgumentException('message2', 2, $e1);
        $e3 = new \Exception('message3', 3, $e2);
        self::assertEquals(new ArrayObject([$e3, $e2, $e1]), Exceptions::getCausalChain($e3));
        $iae = Iterables::filterBy(Exceptions::getCausalChain($e3), '\InvalidArgumentException');
        self::assertSame($e2, Iterables::get($iae, 0));
    }

    /**
     * @test
     */
    public function shouldReturnRootCause()
    {
        $e1 = new \RuntimeException();
        $e2 = new \InvalidArgumentException('message2', 2, $e1);
        $e3 = new \Exception('message3', 3, $e2);
        self::assertSame($e1, Exceptions::getRootCause($e3));
    }

    /**
     * @test
     */
    public function shouldNotPropagate()
    {
        Exceptions::propagateIfInstanceOf(new \Exception(), 'RuntimeException');
        self::assertTrue(true);
    }

    /**
     * @test
     * @expectedException \precore\lang\IllegalStateException
     */
    public function shouldPropagate()
    {
        Exceptions::propagateIfInstanceOf(new IllegalStateException(), 'RuntimeException');
    }
}
