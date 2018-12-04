<?php declare(strict_types=1);

namespace Rector\PHPUnit\Tests\Rector\ExceptionAnnotationRector;

use Rector\PHPUnit\Rector\ExceptionAnnotationRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ExceptionAnnotationRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Wrong/wrong.php.inc',
            __DIR__ . '/Wrong/wrong2.php.inc',
            __DIR__ . '/Wrong/wrong3.php.inc',
            __DIR__ . '/Wrong/wrong4.php.inc',
        ]);
    }

    public function getRectorClass(): string
    {
        return ExceptionAnnotationRector::class;
    }
}
