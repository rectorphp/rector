<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Validation\Collector\EmptyConfigurableRectorCollector;

use Rector\Core\Validation\Collector\EmptyConfigurableRectorCollector;
use Rector\Php80\Rector\Class_\AnnotationToAttributeRector;
use Rector\Testing\PHPUnit\AbstractTestCase;

/**
 * array configurable with missing values config will show warning
 */
final class ConfigurableArrayMissingTest extends AbstractTestCase
{
    private EmptyConfigurableRectorCollector $collector;

    protected function setUp(): void
    {
        $this->bootFromConfigFiles([__DIR__ . '/config/configurable_array_missing.php']);
        $this->collector = $this->getService(EmptyConfigurableRectorCollector::class);
    }

    public function test(): void
    {
        $emptyConfigurableRectors = $this->collector->resolveEmptyConfigurable(
            [$this->getService(AnnotationToAttributeRector::class)]
        );
        $this->assertCount(1, $emptyConfigurableRectors);
    }
}
