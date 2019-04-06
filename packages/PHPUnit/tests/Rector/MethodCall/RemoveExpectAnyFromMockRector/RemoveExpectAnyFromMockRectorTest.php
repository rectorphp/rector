<?php declare(strict_types=1);

namespace Rector\PHPUnit\Tests\Rector\MethodCall\RemoveExpectAnyFromMockRector;

use Rector\PHPUnit\Rector\MethodCall\RemoveExpectAnyFromMockRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RemoveExpectAnyFromMockRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return RemoveExpectAnyFromMockRector::class;
    }
}
