<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\Property\VarToPublicPropertyRector;

use Rector\Php\Rector\Property\VarToPublicPropertyRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class VarToPublicPropertyRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return VarToPublicPropertyRector::class;
    }
}
