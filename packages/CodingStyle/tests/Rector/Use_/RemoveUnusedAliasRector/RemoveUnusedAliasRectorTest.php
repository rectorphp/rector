<?php declare(strict_types=1);

namespace Rector\CodingStyle\Tests\Rector\Use_\RemoveUnusedAliasRector;

use Rector\CodingStyle\Rector\Use_\RemoveUnusedAliasRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RemoveUnusedAliasRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/used.php.inc'];
        yield [__DIR__ . '/Fixture/class_name.php.inc'];
        yield [__DIR__ . '/Fixture/no_namespace.php.inc'];
        yield [__DIR__ . '/Fixture/no_namespace_class_name.php.inc'];
        yield [__DIR__ . '/Fixture/trait_name.php.inc'];
        yield [__DIR__ . '/Fixture/unneeded_trait_name.php.inc'];
        yield [__DIR__ . '/Fixture/interace_extending.php.inc'];
        yield [__DIR__ . '/Fixture/doc_block.php.inc'];
        yield [__DIR__ . '/Fixture/skip_different_namespaces_same_name.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return RemoveUnusedAliasRector::class;
    }
}
