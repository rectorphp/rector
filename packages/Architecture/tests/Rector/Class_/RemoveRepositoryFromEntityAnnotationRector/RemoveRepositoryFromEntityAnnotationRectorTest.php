<?php declare(strict_types=1);

namespace Rector\Architecture\Tests\Rector\Class_\RemoveRepositoryFromEntityAnnotationRector;

use Rector\Architecture\Rector\Class_\RemoveRepositoryFromEntityAnnotationRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RemoveRepositoryFromEntityAnnotationRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideDataForTest()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);
    }

    /**
     * @return string[]
     */
    public function provideDataForTest(): iterable
    {
        yield [__DIR__ . '/Fixture/fixture.php.inc'];
        yield [__DIR__ . '/Fixture/skip_done.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return RemoveRepositoryFromEntityAnnotationRector::class;
    }
}
