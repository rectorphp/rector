<?php declare(strict_types=1);

namespace Rector\PHPUnit\Tests\Rector\ExceptionAnnotationRector;

use Rector\PHPUnit\Rector\ExceptionAnnotationRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ExceptionAnnotationRectorTest extends AbstractRectorTestCase
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
        return ExceptionAnnotationRector::class;
    }
}
