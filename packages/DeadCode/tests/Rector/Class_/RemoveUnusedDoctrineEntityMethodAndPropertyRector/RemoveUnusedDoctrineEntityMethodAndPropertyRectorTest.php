<?php declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\Class_\RemoveUnusedDoctrineEntityMethodAndPropertyRector;

use Rector\DeadCode\Rector\Class_\RemoveUnusedDoctrineEntityMethodAndPropertyRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RemoveUnusedDoctrineEntityMethodAndPropertyRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/remove_inversed_by.php.inc'];
        yield [__DIR__ . '/Fixture/remove_inversed_by_non_fqn.php.inc'];
        yield [__DIR__ . '/Fixture/skip_double_entity_call.php.inc'];
        yield [__DIR__ . '/Fixture/skip_id_and_system.php.inc'];
        yield [__DIR__ . '/Fixture/skip_trait_called_method.php.inc'];
        yield [__DIR__ . '/Fixture/skip_trait_doc_typed.php.inc'];
        yield [__DIR__ . '/Fixture/skip_trait_complex.php.inc'];
        yield [__DIR__ . '/Fixture/skip_abstract_parent.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return RemoveUnusedDoctrineEntityMethodAndPropertyRector::class;
    }
}
