<?php declare(strict_types=1);

namespace Rector\PHPStan\Tests\Rector\Assign\PHPStormVarAnnotationRector;

use Rector\PHPStan\Rector\Assign\PHPStormVarAnnotationRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class PHPStormVarAnnotationRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles(
            [
                __DIR__ . '/Wrong/wrong.php.inc',
                __DIR__ . '/Wrong/wrong2.php.inc',
                __DIR__ . '/Wrong/wrong3.php.inc',
                __DIR__ . '/Wrong/wrong4.php.inc',
            ]
        );
    }

    public function getRectorClass(): string
    {
        return PHPStormVarAnnotationRector::class;
    }
}
