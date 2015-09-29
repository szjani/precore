<?php

namespace precore\util;

use Exception;
use PHPUnit_Framework_TestCase;
use precore\lang\IllegalStateException;
use precore\lang\NullPointerException;

/**
 * Class TryingTest
 *
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class TryingTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @expectedException \precore\lang\IllegalStateException
     */
    public function shouldThrowExceptionIfNotDefined()
    {
        Trying::catchExceptions(['\precore\lang\NullPointerException'])
            ->tryThis($this->throwIseFunction());
    }

    /**
     * @test
     * @expectedException \precore\lang\IllegalStateException
     * @expectedExceptionMessage message
     */
    public function shouldGetThrowOriginalExceptionIfFailure()
    {
        Trying::catchExceptions(['\precore\lang\IllegalStateException'])
            ->tryThis(function () {
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
        $result = Trying::catchExceptions([])
            ->tryThis(Functions::constant($expected))
            ->get();
        self::assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function shouldCallOnFail()
    {
        $called = false;
        $trying = Trying::catchExceptions(['\precore\lang\IllegalStateException'])
            ->tryThis($this->throwIseFunction())
            ->onFail('\Exception', function (IllegalStateException $e) use (&$called) {
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
        Trying::catchExceptions(['\precore\lang\IllegalStateException', '\precore\lang\NullPointerException'])
            ->tryThis($this->throwNpeFunction())
            ->onFail('\precore\lang\IllegalStateException', function (IllegalStateException $e) use (&$iseThrown) {
                $iseThrown = true;
            })
            ->onFail('\precore\lang\NullPointerException', function (NullPointerException $e) use (&$npeThrown) {
                $npeThrown = true;
            });
        self::assertTrue($npeThrown);
        self::assertFalse($iseThrown);

        $iseThrown = false;
        $npeThrown = false;
        Trying::catchExceptions(['\precore\lang\IllegalStateException', '\precore\lang\NullPointerException'])
            ->tryThis($this->throwIseFunction())
            ->onFail('\precore\lang\NullPointerException', function (NullPointerException $e) use (&$npeThrown) {
                $npeThrown = true;
            })
            ->onFail('\precore\lang\IllegalStateException', function (IllegalStateException $e) use (&$iseThrown) {
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
        $res = Trying::runWithCatch($this->throwNpeFunction(), ['\precore\lang\NullPointerException'])->toOptional();
        self::assertFalse($res->isPresent());
    }

    /**
     * @test
     */
    public function shouldSuccessConvertedToOptional()
    {
        $res = Trying::runWithCatch(Functions::constant(1))->toOptional();
        self::assertTrue($res->isPresent());
        self::assertEquals(1, $res->get());
    }

    /**
     * @test
     */
    public function shouldToFailedOptionalReturnPresentIfFailure()
    {
        $res = Trying::runWithCatch($this->throwNpeFunction(), ['\precore\lang\NullPointerException'])
            ->toFailedOptional();
        self::assertTrue($res->isPresent());
        self::assertInstanceOf('\precore\lang\NullPointerException', $res->get());
    }

    /**
     * @test
     */
    public function shouldToFailedOptionalReturnAbsentIfSuccess()
    {
        $res = Trying::runWithCatch(Functions::constant(1))->toFailedOptional();
        self::assertFalse($res->isPresent());
    }

    /**
     * @test
     */
    public function shouldReturnAbsentIfFailureIsFiltered()
    {
        $res = Trying::runWithCatch($this->throwNpeFunction(), ['\precore\lang\NullPointerException'])
            ->filter(Predicates::isNull());
        self::assertFalse($res->isPresent());
    }

    /**
     * @test
     */
    public function shouldReturnOptionalWithFilteredSuccess()
    {
        $trying = Trying::runWithCatch(Functions::constant(1));
        self::assertTrue($trying->filter(Predicates::notNull())->isPresent());
        self::assertFalse($trying->filter(Predicates::isNull())->isPresent());
    }

    /**
     * @test
     */
    public function shouldMapReturnFailure()
    {
        $res = Trying::runWithCatch($this->throwNpeFunction(), ['\precore\lang\NullPointerException'])
            ->map(Functions::constant(1));
        self::assertTrue($res->isFailure());
    }

    /**
     * @test
     */
    public function shouldMapSuccess()
    {
        $res = Trying::runWithCatch(Functions::constant(1))->map(Functions::forMap([1 => 2]));
        self::assertTrue($res->isSuccess());
        self::assertEquals(2, $res->get());
    }

    /**
     * @test
     */
    public function shouldOrElseReturnParamIfFailure()
    {
        $res = Trying::runWithCatch($this->throwNpeFunction(), ['\precore\lang\NullPointerException'])->orElse(1);
        self::assertEquals(1, $res);
    }

    /**
     * @test
     */
    public function shouldOrElseReturnValueIfSuccess()
    {
        $res = Trying::runWithCatch(Functions::constant(1))->orElse(2);
        self::assertEquals(1, $res);
    }

    /**
     * @test
     */
    public function shouldNotCallRecoverIfSuccess()
    {
        $res = Trying::runWithCatch(Functions::constant(1))->recover(null);
        self::assertTrue($res->isSuccess());
        self::assertEquals(1, $res->get());
    }

    /**
     * @test
     */
    public function shouldCallRecoverIfFailure()
    {
        $res = Trying::runWithCatch($this->throwNpeFunction(), ['\precore\lang\NullPointerException'])
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
        $res = Trying::runWithCatch(Functions::constant(1))->orElseGet(null);
        self::assertEquals(1, $res);
    }

    /**
     * @test
     */
    public function shouldCallOrElseGetSupplierIfFailure()
    {
        $res = Trying::runWithCatch($this->throwNpeFunction(), ['\precore\lang\NullPointerException'])
            ->orElseGet(Functions::constant(1));
        self::assertEquals(1, $res);
    }

    /**
     * @test
     */
    public function shouldNotCallFlatMapSupplierIfFailure()
    {
        $res = Trying::runWithCatch($this->throwNpeFunction(), ['\precore\lang\NullPointerException'])
            ->flatMap(Functions::constant(Success::of(1)));
        self::assertTrue($res->isFailure());
    }

    /**
     * @test
     */
    public function shouldCallFlatMapSupplierIfSuccess()
    {
        $res = Trying::runWithCatch(Functions::constant(1))->flatMap(Functions::forMap([1 => Success::of(2)]));
        self::assertTrue($res->equals(Success::of(2)));
    }

    /**
     * @test
     */
    public function shouldRecoverForException()
    {
        $res = Trying::runWithCatch($this->throwNpeFunction(), ['\precore\lang\NullPointerException'])
            ->recoverFor('\precore\lang\IllegalStateException', Functions::constant('npe'))
            ->recoverFor('\precore\lang\NullPointerException', function (NullPointerException $e) {
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
        Trying::catchExceptions(['\precore\lang\IllegalStateException'])
            ->tryThis(function () {
                throw new IllegalStateException("message");
            })
            ->throwException();
    }

    /**
     * @test
     */
    public function shouldFlattenReturnFailureItself()
    {
        $trying = Trying::runWithCatch($this->throwNpeFunction(), ['\precore\lang\NullPointerException']);
        self::assertSame($trying, $trying->flatten());
    }

    /**
     * @test
     */
    public function shouldFlattenReturnLowestTry()
    {
        $failure = Trying::runWithCatch($this->throwNpeFunction(), ['\precore\lang\NullPointerException']);
        $trying = Trying::runWithCatch(Functions::constant($failure));
        self::assertSame($failure, $trying->flatten());
    }

    /**
     * @test
     */
    public function shouldNotThrowException()
    {
        $trying = Trying::runWithCatch(Functions::constant(1));
        $trying->throwException();
        self::assertTrue($trying->isSuccess());
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
