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
        ]);
    }

    protected function getRectorClass(): string
    {
        return RemoveUnusedAliasRector::class;
    }
}
