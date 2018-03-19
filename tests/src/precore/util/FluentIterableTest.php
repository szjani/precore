<?php
declare(strict_types=1);

namespace precore\util;

use PHPUnit\Framework\TestCase;

/**
 * Class FluentIterableTest
 *
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class FluentIterableTest extends TestCase
{
    /**
     * @test
     */
    public function shouldFilterItems()
    {
        $fluentIterable = FluentIterable::of([1, null, 2, 3])->filter(Predicates::notNull());
        self::assertEquals([1, 2, 3], $fluentIterable->toArray());
    }

    /**
     * @test
     */
    public function shouldFilterType()
    {
        $uuid = UUID::randomUUID();
        $fluentIterable = FluentIterable::of([1, null, $uuid, 3])->filterBy(UUID::class);
        self::assertEquals([$uuid], $fluentIterable->toArray());
    }

    /**
     * @test
     */
    public function shouldLimitItems()
    {
        $result = FluentIterable::of([1, 2, 3])->limit(2)->toArray();
        self::assertEquals([1, 2], $result);
    }

    /**
     * @test
     */
    public function shouldTransform()
    {
        $double = function ($number) {
            return 2 * $number;
        };
        $result = FluentIterable::of([1, 2, 3])->transform($double)->toArray();
        self::assertEquals([2, 4, 6], $result);
    }

    /**
     * @test
     */
    public function shouldFilterAfterTransform()
    {
        $double = function ($number) {
            return 2 * $number;
        };
        $smallerThanFive = function ($number) {
            return $number < 5;
        };
        $result = FluentIterable::of([1, 2, 3])->transform($double)->filter($smallerThanFive)->toArray();
        self::assertEquals([2, 4], $result);
    }

    /**
     * @test
     */
    public function shouldRemoveZerosAndNulls()
    {
        $result = FluentIterable::of([1, 0, 3, null, 3, 0, 4])
            ->filter(
                Predicates::ands(
                    Predicates::notNull(),
                    Predicates::not(Predicates::equalTo(0))
                )
            )
            ->toArray();
        self::assertEquals([1, 3, 3, 4], $result);
    }

    /**
     * @test
     */
    public function shouldSkipItems()
    {
        self::assertEquals([2, 3], FluentIterable::of([1, 2, 3])->skip(1)->toArray());
        self::assertEquals([], FluentIterable::of([1, 2, 3])->skip(10)->toArray());
    }

    /**
     * @test
     */
    public function shouldJoinWithJoiner()
    {
        $result = FluentIterable::of([1, null, 2, 3])->join(Joiner::on(', ')->useForNull('null'));
        self::assertEquals('1, null, 2, 3', $result);
    }

    /**
     * @test
     */
    public function shouldReturnFirstElement()
    {
        self::assertTrue(Optional::of(1)->equals(FluentIterable::of([1, 2])->first()));
        self::assertSame(Optional::absent(), FluentIterable::of([])->first());
    }

    /**
     * @test
     */
    public function shouldReturnFirstMatch()
    {
        self::assertTrue(Optional::of(2)->equals(FluentIterable::of([1, 2, 3])->firstMatch(Predicates::equalTo(2))));
        self::assertSame(Optional::absent(), FluentIterable::of([1, 2])->firstMatch(Predicates::equalTo(3)));
        self::assertSame(Optional::absent(), FluentIterable::of([])->firstMatch(Predicates::equalTo(3)));
    }

    /**
     * @test
     */
    public function shouldReturnLast()
    {
        self::assertTrue(Optional::of(2)->equals(FluentIterable::of([1, 2])->last()));
        self::assertSame(Optional::absent(), FluentIterable::of([])->last());
    }

    /**
     * @test
     */
    public function shouldReturnIndex()
    {
        $iterable = FluentIterable::of([1, 2]);
        self::assertEquals(1, $iterable->get(0));
        self::assertEquals(2, $iterable->get(1));
    }

    /**
     * @test
     * @expectedException \OutOfBoundsException
     */
    public function shouldThrowExceptionIfIndexIsInvalid()
    {
        FluentIterable::of([1, 2])->get(2);
    }

    /**
     * @test
     */
    public function shouldReturnToString()
    {
        self::assertEquals('[1, 2]', (string) FluentIterable::of([1, 2]));
    }

    /**
     * @test
     */
    public function shouldContainExistingAndNotContainNonExistingElement()
    {
        $iterable = FluentIterable::of([1, 2]);
        self::assertTrue($iterable->contains(1));
        self::assertFalse($iterable->contains(3));
    }

    /**
     * @test
     */
    public function shouldIsEmptyWork()
    {
        self::assertFalse(FluentIterable::of([1, 2])->isEmpty());
        self::assertTrue(FluentIterable::of([null])->filter(Predicates::notNull())->isEmpty());
    }

    /**
     * @test
     */
    public function shouldAnyMatch()
    {
        $evenPredicate = function ($number) {
            return $number % 2 === 0;
        };
        self::assertTrue(FluentIterable::of([1, 2])->anyMatch($evenPredicate));
        self::assertFalse(FluentIterable::of([1, 3])->anyMatch($evenPredicate));
    }

    /**
     * @test
     */
    public function shouldAllMatch()
    {
        $evenPredicate = function ($number) {
            return $number % 2 === 0;
        };
        self::assertTrue(FluentIterable::of([2, 4])->allMatch($evenPredicate));
        self::assertFalse(FluentIterable::of([2, 4, 1, 6])->allMatch($evenPredicate));
    }

    /**
     * @test
     */
    public function shouldReturnSize()
    {
        self::assertEquals(0, FluentIterable::of([])->size());
        self::assertEquals(1, FluentIterable::of([2])->size());
    }

    /**
     * @test
     */
    public function shouldSort()
    {
        $result = FluentIterable::of(['b', 'a', 'c'])
            ->sorted(StringComparator::$BINARY)
            ->toArray();
        self::assertEquals(['a', 'b', 'c'], $result);
    }

    /**
     * @test
     */
    public function shouldRunEach()
    {
        $array = [];
        FluentIterable::of([3, 1, 2])
            ->filter(
                function ($number) {
                    return $number % 2 === 1;
                }
            )
            ->limit(1)
            ->each(
                function ($number) use (&$array) {
                    $array[] = $number;
                }
            );
        self::assertEquals([3], $array);
    }

    /**
     * @test
     */
    public function shouldAppend()
    {
        $result = FluentIterable::of([1, 2])
            ->append(FluentIterable::of([3, 4]))
            ->toArray();
        self::assertEquals([1, 2, 3, 4], $result);
    }

    /**
     * @test
     */
    public function shouldTransformAndConcat()
    {
        $result = FluentIterable::of([1, 2])
            ->transformAndConcat(
                function ($number) {
                    return FluentIterable::of([$number * 2, $number * 3]);
                }
            )
            ->toArray();
        self::assertEquals([2, 3, 4, 6], $result);
    }

    /**
     * @test
     */
    public function shouldCount()
    {
        self::assertEquals(0, FluentIterable::from(Collections::emptyIterator())->count());
        self::assertEquals(2, FluentIterable::of([0, 3])->count());
    }

    public function testHello()
    {
        $computer = new Computer();
        $soundCard = new SoundCard();
        $usb = new Usb();
        $usb->setVersion(3);
        $soundCard->setUsb($usb);
        $computer->setSoundCard($soundCard);

        self::assertEquals(3, Computer::soundCardUsbVersion($computer));
        self::assertEquals(1, Computer::soundCardUsbVersion(null));

        $versions = [];
        /* @var $c Computer */
        foreach ([$computer, new Computer()] as $c) {
            $version = 1;
            $soundCard = $c->getSoundCard();
            if ($soundCard->isPresent()) {
                $usb = $soundCard->get()->getUsb();
                /* @var $usb Optional */
                if ($usb->isPresent()) {
                    $version = $usb->get()->getVersion();
                }
            }
            $versions[] = $version;
        }
        self::assertEquals([3, 1], $versions);

        $versions = FluentIterable::of([$computer, new Computer()])
            ->map(function (Computer $computer) {return $computer->getSoundCard();})
            ->map(function (Optional $soundCard) {
                return $soundCard
                    ->flatMap(function (SoundCard $soundCard) {return $soundCard->getUsb();})
                    ->map(function (Usb $usb) {return $usb->getVersion();})
                    ->orElse(1);
            })
            ->toArray();
        self::assertEquals([3, 1], $versions);
    }
}

class Computer
{
    private $soundCard;

    public static function soundCardUsbVersion(Computer $computer = null)
    {
        return Optional::ofNullable($computer)
            ->flatMap(function (Computer $computer) {
                return $computer->getSoundCard();
            })
            ->flatMap(function (SoundCard $soundCard) {
                return $soundCard->getUsb();
            })
            ->map(function (Usb $usb) {
                return $usb->getVersion();
            })
            ->orElse(1);
    }

    /**
     * @return Optional
     */
    public function getSoundCard()
    {
        return Optional::ofNullable($this->soundCard);
    }

    /**
     * @param SoundCard $soundCard
     */
    public function setSoundCard(SoundCard $soundCard = null)
    {
        $this->soundCard = $soundCard;
    }
}

class SoundCard
{
    private $usb;

    /**
     * @return Optional
     */
    public function getUsb()
    {
        return Optional::ofNullable($this->usb);
    }

    /**
     * @param Usb $usb
     */
    public function setUsb(Usb $usb = null)
    {
        $this->usb = $usb;
    }
}

class Usb
{
    private $version;

    /**
     * @return int
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param int $version
     */
    public function setVersion($version)
    {
        $this->version = (int) $version;
    }
}
