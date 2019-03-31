<?php declare(strict_types=1);

namespace Rector\Nette\Tests\Rector\NotIdentical\StrposToStringsContainsRector;

use Rector\Nette\Rector\NotIdentical\StrposToStringsContainsRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class StrposToStringsContainsRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc', __DIR__ . '/Fixture/keep.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return StrposToStringsContainsRector::class;
    }
}
