<?php declare(strict_types=1);

namespace Rector\YamlParser\Tests\Rector\Contrib\Symfony;

use Rector\YamlParser\Testing\PHPUnit\AbstractConfigurableYamlRectorTestCase;

final class NamedServiceToClassRectorTest extends AbstractConfigurableYamlRectorTestCase
{
    public function test(): void
    {
        $this->assertStringEqualsFile(
            __DIR__ . '/Source/expected.some_services.yml',
            $this->yamlRectorCollector->processFile(__DIR__ . '/Source/some_services.yml')
        );
    }

    protected function provideConfig(): string
    {
        return __DIR__ . '/Source/config.yml';
    }
}
