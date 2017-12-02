<?php declare(strict_types=1);

namespace Rector\YamlParser\Tests\Rector\Contrib\Symfony;

use PHPUnit\Framework\TestCase;
use Rector\DependencyInjection\ContainerFactory;
use Rector\YamlParser\YamlRectorCollector;

final class NamedServiceToClassRectorTest extends TestCase
{
    /**
     * @var YamlRectorCollector
     */
    private $yamlRectorCollector;

    protected function setUp(): void
    {
        $container = (new ContainerFactory())->createWithConfig($this->provideConfig());
        $this->yamlRectorCollector = $container->get(YamlRectorCollector::class);
    }

    public function test(): void
    {
        $this->assertStringEqualsFile(
            __DIR__ . '/Source/expected.some_services.yml',
            $this->yamlRectorCollector->processFile(__DIR__ . '/Source/some_services.yml')
        );
    }

    private function provideConfig(): string
    {
        return __DIR__ . '/Source/config.yml';
    }
}
