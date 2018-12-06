<?php declare(strict_types=1);

namespace Rector\PHPStan\Tests\Rector\Assign\PHPStormVarAnnotationRector;

use Rector\PHPStan\Rector\Assign\PHPStormVarAnnotationRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class PHPStormVarAnnotationRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/fixture.php.inc',
            __DIR__ . '/Fixture/fixture2.php.inc',
            __DIR__ . '/Fixture/fixture3.php.inc',
            __DIR__ . '/Fixture/fixture4.php.inc',
        ]);
    }

    public function getRectorClass(): string
    {
        return PHPStormVarAnnotationRector::class;
    }
}
