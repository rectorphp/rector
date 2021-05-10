<?php

declare (strict_types=1);
namespace RectorPrefix20210510\Symplify\SymplifyKernel\Tests\Console\AbstractSymplifyConsoleApplication;

use RectorPrefix20210510\Symfony\Component\Console\Application;
use RectorPrefix20210510\Symplify\PackageBuilder\Testing\AbstractKernelTestCase;
use RectorPrefix20210510\Symplify\SymplifyKernel\Tests\HttpKernel\OnlyForTestsKernel;
final class AutowiredConsoleApplicationTest extends AbstractKernelTestCase
{
    protected function setUp() : void
    {
        $this->bootKernel(OnlyForTestsKernel::class);
    }
    public function test() : void
    {
        $application = $this->getService(Application::class);
        $this->assertInstanceOf(Application::class, $application);
    }
}
