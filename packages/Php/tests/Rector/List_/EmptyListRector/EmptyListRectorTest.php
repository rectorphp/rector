<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\List_\EmptyListRector;

use Rector\Php\Rector\List_\EmptyListRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class EmptyListRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFilesWithoutAutoload([__DIR__ . '/Fixture/fixture.php.inc']);
    }

    public function getRectorClass(): string
    {
        return EmptyListRector::class;
    }
}
