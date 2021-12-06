<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Validation\Collector\EmptyConfigurableRectorCollector;

use Rector\Core\Validation\Collector\EmptyConfigurableRectorCollector;
use Rector\Testing\PHPUnit\AbstractTestCase;

final class EmptyConfigurableRectorCollectorTest extends AbstractTestCase
{
    private EmptyConfigurableRectorCollector $emptyConfigurableRectorCollector;

    protected function setUp(): void
    {
        $this->bootFromConfigFiles([__DIR__ . '/config/configurable_array_has_values.php']);
        $this->emptyConfigurableRectorCollector = $this->getService(EmptyConfigurableRectorCollector::class);
    }

    public function test(): void
    {
        $emptyConfigurableRectors = $this->emptyConfigurableRectorCollector->resolveEmptyConfigurableRectorClasses();
        $this->assertCount(0, $emptyConfigurableRectors);
    }
}
