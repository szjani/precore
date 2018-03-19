<?php
declare(strict_types=1);

namespace precore\util;

use Go\Core\AspectContainer;
use Go\Core\AspectKernel;
use PHPUnit\Framework\TestCase;

/**
 * Class ProfileLogAspectTest
 *
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class ProfileLogAspectTest extends TestCase
{
    public function testProfile()
    {
        $kernel = ProfileLogAspectKernel::getInstance();
        $kernel->init(
            [
                'debug' => true,
                'cacheDir' => BASEDIR . '/build/cache',
                'includePaths' => [__DIR__]
            ]
        );
        $fixture = new ProfileFixture();
        $result = $fixture->main();
        self::assertEquals(ProfileFixture::RETURN_VALUE, $result);
    }
}

class ProfileLogAspectKernel extends AspectKernel
{

    /**
     * Configure an AspectContainer with advisors, aspects and pointcuts
     *
     * @param AspectContainer $container
     *
     * @return void
     */
    protected function configureAop(AspectContainer $container)
    {
        $container->registerAspect(new ProfileLogAspect());
    }
}
