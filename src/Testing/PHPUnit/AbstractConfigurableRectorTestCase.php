<?php declare(strict_types=1);

namespace Rector\Testing\PHPUnit;

use Rector\Application\FileProcessor;
use Rector\DependencyInjection\ContainerFactory;
use Symplify\PackageBuilder\Parameter\ParameterProvider;

abstract class AbstractConfigurableRectorTestCase extends AbstractRectorTestCase
{
    protected function setUp(): void
    {
        $this->container = (new ContainerFactory())->createWithConfig($this->provideConfig());
        $this->fileProcessor = $this->container->get(FileProcessor::class);
        $this->parameterProvider = $this->container->get(ParameterProvider::class);
    }

    abstract protected function provideConfig(): string;
}
