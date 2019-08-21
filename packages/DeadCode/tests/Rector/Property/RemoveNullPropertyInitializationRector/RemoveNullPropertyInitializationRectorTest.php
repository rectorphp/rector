<?php declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\Property\RemoveNullPropertyInitializationRector;

use Rector\DeadCode\Rector\Property\RemoveNullPropertyInitializationRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RemoveNullPropertyInitializationRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return RemoveNullPropertyInitializationRector::class;
    }
}
