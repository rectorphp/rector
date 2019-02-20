<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\FuncCall\RemoveReferenceFromCallRector;

use Rector\Php\Rector\FuncCall\RemoveReferenceFromCallRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RemoveReferenceFromCallRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFilesWithoutAutoload([__DIR__ . '/Fixture/fixture.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return RemoveReferenceFromCallRector::class;
    }
}
