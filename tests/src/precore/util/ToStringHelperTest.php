<?php
declare(strict_types=1);

namespace precore\util;

use DateTime;
use PHPUnit\Framework\TestCase;
use precore\util\error\ErrorHandler;

class ToStringHelperTest extends TestCase
{
    /**
     * @test
     */
    public function oneNonNullProperty()
    {
        $obj = UUID::randomUUID();
        $helper = new ToStringHelper($obj->getClassName());
        $value = $obj->toString();
        $string = $helper
            ->add('value', $value)
            ->toString();
        self::assertEquals($obj->getClassName() . "{value=$value}", $string);
    }

    /**
     * @test
     */
    public function omitNullValues()
    {
        $helper = new ToStringHelper(__CLASS__);
        $string = $helper
            ->add('x', null)
            ->add('y', 'hello')
            ->omitNullValues()
            ->toString();
        self::assertEquals(sprintf('%s{y=hello}', __CLASS__), $string);
    }

    /**
     *  @test
     */
    public function shouldOmitNullValuesOnlyInCaseOfMemberVariables()
    {
        $helper = new ToStringHelper(__CLASS__);
        $string = $helper
            ->add('x', null)
            ->add('y', ['notNull' => 1, 'null' => null])
            ->omitNullValues()
            ->toString();
        self::assertEquals(sprintf('%s{y=[notNull=1, null=null]}', __CLASS__), $string);
    }

    /**
     * @test
     */
    public function nullValueAppear()
    {
        $helper = new ToStringHelper(__CLASS__);
        $string = $helper
            ->add('x', null)
            ->add('y', 'hello')
            ->toString();
        self::assertEquals(sprintf('%s{x=null, y=hello}', __CLASS__), $string);
    }

    /**
     * @test
     */
    public function noFields()
    {
        $helper = new ToStringHelper(__CLASS__);
        $string = $helper->toString();
        self::assertEquals(sprintf('%s{}', __CLASS__), $string);
    }

    public function testDates()
    {
        $helper = new ToStringHelper(__CLASS__);
        $now = new DateTime();
        $result = $helper
            ->add('date', $now)
            ->toString();
        self::assertTrue(strpos($result, $now->format(DateTime::ISO8601)) !== false);
    }

    public function testStringCastError()
    {
        ErrorHandler::register();
        $helper = new ToStringHelper(__CLASS__);
        $object = new \stdClass();
        $result = $helper
            ->add('object', $object)
            ->toString();
        restore_error_handler();
        self::assertRegExp('/' . spl_object_hash($object) . '/', $result);
    }

    public function testArrayProperty()
    {
        $helper = new ToStringHelper(__CLASS__);
        $result = $helper
            ->add('fields', [1, new DateTime()])
            ->toString();
        self::assertRegExp('/fields=\[0=1, 1=/', $result);
    }

    /**
     * @test
     */
    public function shouldSupportNoKeys()
    {
        $helper = new ToStringHelper(__CLASS__);
        $string = $helper
            ->add('x', null)
            ->add(3)
            ->add(['notNull' => 1, 'null' => null])
            ->toString();
        self::assertEquals(sprintf('%s{x=null, 3, [notNull=1, null=null]}', __CLASS__), $string);
    }

    /**
     * @test
     */
    public function shouldCallToStringOnIteratorIfPossible()
    {
        $obj = new ToStringOverriddenIterator(new \EmptyIterator());
        self::assertEquals('okay', ToStringHelper::valueToString($obj));
    }
}

class ToStringOverriddenIterator extends \IteratorIterator
{
    public function __toString()
    {
        return 'okay';
    }
}
