<?php
declare(strict_types=1);

namespace precore\util;

use PHPUnit\Framework\TestCase;
use precore\lang\IllegalStateException;
use precore\lang\NullPointerException;

/**
 * Class TryToTest
 *
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class TryToTest extends TestCase
{
    /**
     * @test
     * @expectedException \precore\lang\IllegalStateException
     */
    public function shouldThrowExceptionIfNotDefined()
    {
        TryTo::catchExceptions([NullPointerException::class])
            ->run($this->throwIseFunction());
    }

    /**
     * @test
     * @expectedException \precore\lang\IllegalStateException
     * @expectedExceptionMessage message
     */
    public function shouldGetThrowOriginalExceptionIfFailure()
    {
        TryTo::catchExceptions()
            ->run(function () {
                throw new IllegalStateException("message");
            })
            ->get();
    }

    /**
     * @test
     */
    public function shouldReturnValueIfNoException()
    {
        $expected = 'value';
        $result = TryTo::catchExceptions()
            ->run(Functions::constant($expected))
            ->get();
        self::assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function shouldCallOnFail()
    {
        $called = false;
        $trying = TryTo::catchExceptions()
            ->run($this->throwIseFunction())
            ->onFail(IllegalStateException::class, function (IllegalStateException $e) use (&$called) {
                $called = true;
            });
        self::assertTrue($called);
        self::assertTrue($trying->isFailure());
        self::assertFalse($trying->isSuccess());
    }

    /**
     * @test
     */
    public function shouldSupportMultipleOnFails()
    {
        $iseThrown = false;
        $npeThrown = false;
        TryTo::catchExceptions()
            ->run($this->throwNpeFunction())
            ->onFail(IllegalStateException::class, function (IllegalStateException $e) use (&$iseThrown) {
                $iseThrown = true;
            })
            ->onFail(NullPointerException::class, function (NullPointerException $e) use (&$npeThrown) {
                $npeThrown = true;
            });
        self::assertTrue($npeThrown);
        self::assertFalse($iseThrown);

        $iseThrown = false;
        $npeThrown = false;
        TryTo::catchExceptions()
            ->run($this->throwIseFunction())
            ->onFail(NullPointerException::class, function (NullPointerException $e) use (&$npeThrown) {
                $npeThrown = true;
            })
            ->onFail(IllegalStateException::class, function (IllegalStateException $e) use (&$iseThrown) {
                $iseThrown = true;
            });
        self::assertTrue($iseThrown);
        self::assertFalse($npeThrown);
    }

    /**
     * @test
     */
    public function shouldFailConvertedToAbsent()
    {
        $res = TryTo::run($this->throwNpeFunction())->toOptional();
        self::assertFalse($res->isPresent());
    }

    /**
     * @test
     */
    public function shouldSuccessConvertedToOptional()
    {
        $res = TryTo::run(Functions::constant(1))->toOptional();
        self::assertTrue($res->isPresent());
        self::assertEquals(1, $res->get());
    }

    /**
     * @test
     */
    public function shouldToFailedOptionalReturnPresentIfFailure()
    {
        $res = TryTo::run($this->throwNpeFunction())->toFailedOptional();
        self::assertTrue($res->isPresent());
        self::assertInstanceOf(NullPointerException::class, $res->get());
    }

    /**
     * @test
     */
    public function shouldToFailedOptionalReturnAbsentIfSuccess()
    {
        $res = TryTo::run(Functions::constant(1))->toFailedOptional();
        self::assertFalse($res->isPresent());
    }

    /**
     * @test
     */
    public function shouldNotThrowExceptionIfHandledExceptionListIsEmpty()
    {
        $trying = TryTo::run($this->throwNpeFunction());
        self::assertTrue($trying->isFailure());
    }

    /**
     * @test
     */
    public function shouldReturnAbsentIfFailureIsFiltered()
    {
        $res = TryTo::run($this->throwNpeFunction())
            ->filter(Predicates::isNull());
        self::assertFalse($res->isPresent());
    }

    /**
     * @test
     */
    public function shouldReturnOptionalWithFilteredSuccess()
    {
        $trying = TryTo::run(Functions::constant(1));
        self::assertTrue($trying->filter(Predicates::notNull())->isPresent());
        self::assertFalse($trying->filter(Predicates::isNull())->isPresent());
    }

    /**
     * @test
     */
    public function shouldMapReturnFailure()
    {
        $res = TryTo::run($this->throwNpeFunction())
            ->map(Functions::constant(1));
        self::assertTrue($res->isFailure());
    }

    /**
     * @test
     */
    public function shouldMapSuccess()
    {
        $res = TryTo::run(Functions::constant(1))->map(Functions::forMap([1 => 2]));
        self::assertTrue($res->isSuccess());
        self::assertEquals(2, $res->get());
    }

    /**
     * @test
     */
    public function shouldOrElseReturnParamIfFailure()
    {
        $res = TryTo::run($this->throwNpeFunction())->orElse(1);
        self::assertEquals(1, $res);
    }

    /**
     * @test
     */
    public function shouldOrElseReturnValueIfSuccess()
    {
        $res = TryTo::run(Functions::constant(1))->orElse(2);
        self::assertEquals(1, $res);
    }

    /**
     * @test
     */
    public function shouldNotCallRecoverIfSuccess()
    {
        $res = TryTo::run(Functions::constant(1))->recover(null);
        self::assertTrue($res->isSuccess());
        self::assertEquals(1, $res->get());
    }

    /**
     * @test
     */
    public function shouldCallRecoverIfFailure()
    {
        $res = TryTo::run($this->throwNpeFunction())
            ->recover(function (NullPointerException $e) {
                return 1;
            });
        self::assertTrue($res->isSuccess());
        self::assertEquals(1, $res->get());
    }

    /**
     * @test
     */
    public function shouldNotCallOrElseGetSupplierIfSuccess()
    {
        $res = TryTo::run(Functions::constant(1))->orElseGet(null);
        self::assertEquals(1, $res);
    }

    /**
     * @test
     */
    public function shouldCallOrElseGetSupplierIfFailure()
    {
        $res = TryTo::run($this->throwNpeFunction())
            ->orElseGet(Functions::constant(1));
        self::assertEquals(1, $res);
    }

    /**
     * @test
     */
    public function shouldNotCallFlatMapSupplierIfFailure()
    {
        $res = TryTo::run($this->throwNpeFunction())
            ->flatMap(Functions::constant(Success::of(1)));
        self::assertTrue($res->isFailure());
    }

    /**
     * @test
     */
    public function shouldCallFlatMapSupplierIfSuccess()
    {
        $res = TryTo::run(Functions::constant(1))->flatMap(Functions::forMap([1 => Success::of(2)]));
        self::assertTrue($res->equals(Success::of(2)));
    }

    /**
     * @test
     */
    public function shouldRecoverForException()
    {
        $res = TryTo::run($this->throwNpeFunction())
            ->recoverFor(IllegalStateException::class, Functions::constant('ise'))
            ->recoverFor(NullPointerException::class, function (NullPointerException $e) {
                return 'npe';
            });
        self::assertTrue($res->isSuccess());
        self::assertEquals('npe', $res->get());
    }

    /**
     * @test
     * @expectedException \precore\lang\IllegalStateException
     * @expectedExceptionMessage message
     */
    public function shouldThrowException()
    {
        TryTo::catchExceptions()
            ->run(function () {
                throw new IllegalStateException("message");
            })
            ->throwException();
    }

    /**
     * @test
     */
    public function shouldFlattenReturnFailureItself()
    {
        $trying = TryTo::run($this->throwNpeFunction());
        self::assertSame($trying, $trying->flatten());
    }

    /**
     * @test
     */
    public function shouldFlattenReturnLowestTry()
    {
        $failure = TryTo::run($this->throwNpeFunction());
        $trying = TryTo::run(Functions::constant($failure));
        self::assertSame($failure, $trying->flatten());
    }

    /**
     * @test
     */
    public function shouldNotThrowException()
    {
        $trying = TryTo::run(Functions::constant(1));
        $trying->throwException();
        self::assertTrue($trying->isSuccess());
    }

    /**
     * @test
     */
    public function shouldRunFinally()
    {
        $finallyCalled = false;
        $trying = TryTo::catchExceptions()
            ->whenRun($this->throwNpeFunction())
            ->andFinally(
                function () use (&$finallyCalled) {
                    $finallyCalled = true;
                }
            );
        self::assertTrue($trying->isFailure());
        self::assertTrue($finallyCalled);
    }

    /**
     * @return \Closure
     */
    private function throwNpeFunction()
    {
        return function () {
            throw new NullPointerException();
        };
    }

    /**
     * @return \Closure
     */
    private function throwIseFunction()
    {
        return function () {
            throw new IllegalStateException();
        };
    }
}
