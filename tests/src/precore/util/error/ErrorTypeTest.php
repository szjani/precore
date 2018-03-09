<?php

namespace precore\util\error;

use PHPUnit\Framework\TestCase;

class ErrorTypeTest extends TestCase
{
    public function testForCode()
    {
        $error = ErrorType::forCode(E_ERROR);
        self::assertEquals(E_ERROR, $error->getCode());
    }

    /**
     * @expectedException \Exception
     */
    public function testInvalidForCode()
    {
        ErrorType::forCode(0);
    }

    /**
     * @expectedException \precore\util\error\UserWarningException
     */
    public function testThrowException()
    {
        ErrorType::forCode(E_USER_WARNING)->throwException('Ouch', __FILE__, __LINE__, []);
    }
}
