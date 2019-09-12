<?php declare(strict_types=1);

namespace Rector\Symfony\Tests\Rector\MethodCall\CascadeValidationFormBuilderRector;

use Rector\Symfony\Rector\MethodCall\CascadeValidationFormBuilderRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class CascadeValidationFormBuilderRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideDataForTest()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);
    }

    public function getRectorClass(): string
    {
        return CascadeValidationFormBuilderRector::class;
    }

    /**
     * @return string[]
     */
    public function provideDataForTest(): iterable
    {
        yield [__DIR__ . '/Fixture/fixture.php.inc'];
    }
}
