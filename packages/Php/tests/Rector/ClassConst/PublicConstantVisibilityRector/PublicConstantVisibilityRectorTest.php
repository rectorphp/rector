<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\ClassConst\PublicConstantVisibilityRector;

use Rector\Php\Rector\ClassConst\PublicConstantVisibilityRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class PublicConstantVisibilityRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([[__DIR__ . '/Wrong/SomeClass.php', __DIR__ . '/Correct/correct.php.inc']]);
    }

    public function getRectorClass(): string
    {
        return PublicConstantVisibilityRector::class;
    }
}
