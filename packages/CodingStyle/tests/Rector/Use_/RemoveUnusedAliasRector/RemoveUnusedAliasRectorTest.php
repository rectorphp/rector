<?php declare(strict_types=1);

namespace Rector\CodingStyle\Tests\Rector\Use_\RemoveUnusedAliasRector;

use Rector\CodingStyle\Rector\Use_\RemoveUnusedAliasRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RemoveUnusedAliasRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/fixture.php.inc',
            __DIR__ . '/Fixture/used.php.inc',
            __DIR__ . '/Fixture/class_name.php.inc',
            # no namespace
            __DIR__ . '/Fixture/no_namespace.php.inc',
            __DIR__ . '/Fixture/no_namespace_class_name.php.inc',
            # traits
            __DIR__ . '/Fixture/trait_name.php.inc',
            __DIR__ . '/Fixture/unneeded_trait_name.php.inc',
            # interfaces
            __DIR__ . '/Fixture/interace_extending.php.inc',
            __DIR__ . '/Fixture/doc_block.php.inc',
        ]);
    }

    protected function getRectorClass(): string
    {
        return RemoveUnusedAliasRector::class;
    }
}
