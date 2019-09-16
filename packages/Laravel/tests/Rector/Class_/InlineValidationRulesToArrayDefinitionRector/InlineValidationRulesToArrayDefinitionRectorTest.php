<?php declare(strict_types=1);

namespace Rector\Laravel\Tests\Rector\Class_\InlineValidationRulesToArrayDefinitionRector;

use Rector\Laravel\Rector\Class_\InlineValidationRulesToArrayDefinitionRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class InlineValidationRulesToArrayDefinitionRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFile(__DIR__ . '/Fixture/fixture.php.inc');
    }

    protected function getRectorClass(): string
    {
        return InlineValidationRulesToArrayDefinitionRector::class;
    }
}
