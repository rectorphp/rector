<?php declare(strict_types=1);

namespace Rector\PHPUnit\Tests\Rector\GetMockRector;

use Rector\PHPUnit\Rector\GetMockRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class GetMockRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc']);
    }

    public function getRectorClass(): string
    {
        return GetMockRector::class;
    }
}
