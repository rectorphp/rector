<?php declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\Expression\SimplifyMirrorAssignRector;

use Rector\CodeQuality\Rector\Expression\SimplifyMirrorAssignRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class SimplifyMirrorAssignRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Wrong/wrong.php.inc']);
    }

    public function getRectorClass(): string
    {
        return SimplifyMirrorAssignRector::class;
    }
}
