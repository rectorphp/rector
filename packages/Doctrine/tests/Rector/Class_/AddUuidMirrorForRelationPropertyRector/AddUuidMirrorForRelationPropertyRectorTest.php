<?php declare(strict_types=1);

namespace Rector\Doctrine\Tests\Rector\Class_\AddUuidMirrorForRelationPropertyRector;

use Rector\Doctrine\Rector\Class_\AddUuidMirrorForRelationPropertyRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class AddUuidMirrorForRelationPropertyRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/to_one.php.inc'];
        yield [__DIR__ . '/Fixture/to_many.php.inc'];
        yield [__DIR__ . '/Fixture/many_to_many_with_extra_name.php.inc'];
        // skip
        yield [__DIR__ . '/Fixture/skip_already_added.php.inc'];
        yield [__DIR__ . '/Fixture/skip_to_many_without_target_entity_uuid.php.inc'];
        yield [__DIR__ . '/Fixture/skip_one_to_one_mapped_by.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return AddUuidMirrorForRelationPropertyRector::class;
    }
}
