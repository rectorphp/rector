<?php declare(strict_types=1);

namespace Rector\Laravel\Tests\Rector\Class_\InlineValidationRulesToArrayDefinitionRector;

use Rector\Laravel\Rector\Class_\InlineValidationRulesToArrayDefinitionRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class InlineValidationRulesToArrayDefinitionRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideForTest()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);
    }

    public function provideForTest(): iterable
    {
        yield [__DIR__ . '/Fixture/fixture.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return InlineValidationRulesToArrayDefinitionRector::class;
    }
}
