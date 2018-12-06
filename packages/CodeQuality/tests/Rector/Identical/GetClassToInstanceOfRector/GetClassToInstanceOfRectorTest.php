<?php declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\Identical\GetClassToInstanceOfRector;

use Rector\CodeQuality\Rector\Identical\GetClassToInstanceOfRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class GetClassToInstanceOfRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc']);
    }

    public function getRectorClass(): string
    {
        return GetClassToInstanceOfRector::class;
    }
}
