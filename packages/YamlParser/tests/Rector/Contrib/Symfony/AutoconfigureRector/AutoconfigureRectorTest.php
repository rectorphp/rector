<?php declare(strict_types=1);

namespace Rector\YamlParser\Tests\Rector\Contrib\Symfony\AutoconfigureRector;

use Rector\YamlParser\Testing\PHPUnit\AbstractConfigurableYamlRectorTestCase;

final class AutoconfigureRectorTest extends AbstractConfigurableYamlRectorTestCase
{
    public function test(): void
    {
        $this->assertStringEqualsFile(
            __DIR__ . '/Source/services_after.yml',
            $this->yamlRectorCollector->processFile(__DIR__ . '/Source/services_before.yml')
        );
    }

    protected function provideConfig(): string
    {
        return __DIR__ . '/Source/config.yml';
    }
}
