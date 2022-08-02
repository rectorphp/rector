<?php

declare (strict_types=1);
namespace RectorPrefix202208\Symplify\SymplifyKernel\Tests\ContainerBuilderFactory;

use PHPUnit\Framework\TestCase;
use RectorPrefix202208\Symplify\SmartFileSystem\SmartFileSystem;
use RectorPrefix202208\Symplify\SymplifyKernel\Config\Loader\ParameterMergingLoaderFactory;
use RectorPrefix202208\Symplify\SymplifyKernel\ContainerBuilderFactory;
final class ContainerBuilderFactoryTest extends TestCase
{
    public function test() : void
    {
        $containerBuilderFactory = new ContainerBuilderFactory(new ParameterMergingLoaderFactory());
        $containerBuilder = $containerBuilderFactory->create([__DIR__ . '/config/some_services.php'], [], []);
        $hasSmartFileSystemService = $containerBuilder->has(SmartFileSystem::class);
        $this->assertTrue($hasSmartFileSystemService);
    }
}
