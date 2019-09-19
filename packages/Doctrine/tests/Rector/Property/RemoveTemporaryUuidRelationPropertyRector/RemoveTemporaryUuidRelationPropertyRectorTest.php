<?php declare(strict_types=1);

namespace Rector\Doctrine\Tests\Rector\Property\RemoveTemporaryUuidRelationPropertyRector;

use Rector\Doctrine\Rector\Property\RemoveTemporaryUuidRelationPropertyRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RemoveTemporaryUuidRelationPropertyRectorTest extends AbstractRectorTestCase
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
    }

    protected function getRectorClass(): string
    {
        return RemoveTemporaryUuidRelationPropertyRector::class;
    }
}
