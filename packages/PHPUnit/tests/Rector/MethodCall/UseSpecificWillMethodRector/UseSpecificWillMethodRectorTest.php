<?php declare(strict_types=1);

namespace Rector\PHPUnit\Tests\Rector\MethodCall\UseSpecificWillMethodRector;

use Rector\PHPUnit\Rector\MethodCall\UseSpecificWillMethodRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class UseSpecificWillMethodRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/will.php.inc', __DIR__ . '/Fixture/with.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return UseSpecificWillMethodRector::class;
    }
}
