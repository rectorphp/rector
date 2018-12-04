<?php declare(strict_types=1);

namespace Rector\PHPStan\Tests\Rector\Cast\RecastingRemovalRector;

use Rector\PHPStan\Rector\Cast\RecastingRemovalRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RecastingRemovalRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Wrong/wrong.php.inc', __DIR__ . '/Wrong/wrong2.php.inc']);
    }

    public function getRectorClass(): string
    {
        return RecastingRemovalRector::class;
    }
}
