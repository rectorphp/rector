<?php declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\FunctionLike\RemoveCodeAfterReturnRector;

use Rector\DeadCode\Rector\FunctionLike\RemoveCodeAfterReturnRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RemoveCodeAfterReturnRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc', __DIR__ . '/Fixture/keep_nested.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return RemoveCodeAfterReturnRector::class;
    }
}
