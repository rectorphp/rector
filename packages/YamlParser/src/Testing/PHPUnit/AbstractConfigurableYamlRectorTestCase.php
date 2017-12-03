<?php declare(strict_types=1);

namespace Rector\YamlParser\Testing\PHPUnit;

use PHPUnit\Framework\TestCase;
use Rector\DependencyInjection\ContainerFactory;
use Rector\YamlParser\YamlRectorCollector;

abstract class AbstractConfigurableYamlRectorTestCase extends TestCase
{
    /**
     * @var YamlRectorCollector
     */
    protected $yamlRectorCollector;

    protected function setUp(): void
    {
        $container = (new ContainerFactory())->createWithConfig($this->provideConfig());
        $this->yamlRectorCollector = $container->get(YamlRectorCollector::class);
    }

    abstract protected function provideConfig(): string;
}
