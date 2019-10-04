<?php declare(strict_types=1);

namespace Rector\SOLID\Tests\Rector\ClassConst\PrivatizeLocalClassConstantRector;

use Iterator;
use Rector\SOLID\Rector\ClassConst\PrivatizeLocalClassConstantRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class KeepDeclaredVisibilityTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideDataForTest()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);
    }

    public function provideDataForTest(): Iterator
    {
        yield [__DIR__ . '/Fixture/fixture.php.inc'];
        yield [__DIR__ . '/Fixture/keep_public.php.inc'];
        yield [__DIR__ . '/Fixture/keep_declared_visibility.php.inc'];
    }

    protected function getRectorsWithConfiguration(): array
    {
        return [
            PrivatizeLocalClassConstantRector::class => [
                '$keepDeclaredVisibility' => true,
            ],
        ];
    }
}
