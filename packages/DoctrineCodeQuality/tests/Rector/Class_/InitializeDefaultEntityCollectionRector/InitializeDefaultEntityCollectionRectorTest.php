<?php declare(strict_types=1);

namespace Rector\DoctrineCodeQuality\Tests\Rector\Class_\InitializeDefaultEntityCollectionRector;

use Iterator;
use Rector\DoctrineCodeQuality\Rector\Class_\InitializeDefaultEntityCollectionRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class InitializeDefaultEntityCollectionRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/existing_constructor.php.inc'];
        yield [__DIR__ . '/Fixture/skip_added.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return InitializeDefaultEntityCollectionRector::class;
    }
}
