<?php declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\Class_\RemoveUnusedDoctrineEntityMethodAndPropertyRector;

use Rector\DeadCode\Rector\Class_\RemoveUnusedDoctrineEntityMethodAndPropertyRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RemoveUnusedDoctrineEntityMethodAndPropertyRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/fixture.php.inc',
            __DIR__ . '/Fixture/remove_inversed_by.php.inc',
            __DIR__ . '/Fixture/remove_inversed_by_non_fqn.php.inc',
            // skip
            __DIR__ . '/Fixture/skip_double_entity_call.php.inc',
            __DIR__ . '/Fixture/skip_id_and_system.php.inc',
            __DIR__ . '/Fixture/skip_trait_called_method.php.inc',
            __DIR__ . '/Fixture/skip_trait_doc_typed.php.inc',
        ]);
    }

    protected function getRectorClass(): string
    {
        return RemoveUnusedDoctrineEntityMethodAndPropertyRector::class;
    }
}
