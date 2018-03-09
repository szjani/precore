<?php
declare(strict_types=1);

namespace precore\util;

use PHPUnit\Framework\TestCase;

/**
 * Description of UUIDTest
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class UUIDTest extends TestCase
{
    public function testRandomUUID()
    {
        $uuid1 = UUID::randomUUID();
        $uuid2 = UUID::randomUUID();
        self::assertFalse($uuid1->equals($uuid2));
        self::assertNotEquals($uuid2, $uuid1);
    }

    public function testSerialize()
    {
        $uuid = UUID::randomUUID();
        $value = $uuid->toString();
        self::assertNotEmpty($value);
        $ser = serialize($uuid);
        $deser = unserialize($ser);
        self::assertTrue($uuid->equals($deser));
    }

    public function testFromString()
    {
        $uuid = UUID::randomUUID();
        $res = UUID::fromString($uuid->toString());
        self::assertEquals($uuid, $res);
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function shouldFailWithInvalidString()
    {
        UUID::fromString('invalid');
    }
}
