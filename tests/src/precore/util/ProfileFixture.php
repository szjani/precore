<?php
declare(strict_types=1);

namespace precore\util;

// required for GO AOP-PHP, even if it is under the same namespace!
use precore\util\Profile;

/**
 * Class ProfileFixture
 *
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class ProfileFixture
{
    const RETURN_VALUE = 10;

    /**
     * @Profile(name="Main process")
     */
    public function main()
    {
        $this->foo1();
        $this->foo2();
        return self::RETURN_VALUE;
    }

    /**
     * @Profile
     */
    protected function foo1()
    {
        $this->bar();
    }

    /**
     * @Profile
     */
    protected function bar()
    {
    }

    /**
     * @Profile(name="very fast method")
     */
    protected function foo2()
    {
    }
}
