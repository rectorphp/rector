<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Validation\Collector\EmptyConfigurableRectorCollector;

use Rector\Core\Validation\Collector\EmptyConfigurableRectorCollector;
use Rector\Php80\Rector\Class_\AnnotationToAttributeRector;
use Rector\Testing\PHPUnit\AbstractTestCase;

/**
 * array configurable with has values config will be passed
 */
final class ConfigurableArrayHasValuesTest extends AbstractTestCase
{
    private EmptyConfigurableRectorCollector $collector;

    protected function setUp(): void
    {
        $this->bootFromConfigFiles([__DIR__ . '/config/configurable_array_has_values.php']);
        $this->collector = $this->getService(EmptyConfigurableRectorCollector::class);
    }

    public function test(): void
    {
        $emptyConfigurableRectors = $this->collector->resolveEmptyConfigurable(
            [$this->getService(AnnotationToAttributeRector::class)]
        );
        $this->assertCount(0, $emptyConfigurableRectors);
    }
}
