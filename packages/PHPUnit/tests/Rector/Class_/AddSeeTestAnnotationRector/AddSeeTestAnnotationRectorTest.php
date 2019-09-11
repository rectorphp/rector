<?php declare(strict_types=1);

namespace Rector\PHPUnit\Tests\Rector\Class_\AddSeeTestAnnotationRector;

use Rector\PHPUnit\Rector\Class_\AddSeeTestAnnotationRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class AddSeeTestAnnotationRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/different_namespace.php.inc'];
        yield [__DIR__ . '/Fixture/add_to_doc_block.php.inc'];
        yield [__DIR__ . '/Fixture/skip_existing.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return AddSeeTestAnnotationRector::class;
    }
}
