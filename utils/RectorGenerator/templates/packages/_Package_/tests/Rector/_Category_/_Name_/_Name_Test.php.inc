<?php declare(strict_types=1);

namespace Rector\_Package_\Tests\Rector\_Category_\_Name_;

use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class _Name_Test extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/fixture.php.inc'
        ]);
    }

    protected function getRectorClass(): string
    {
        return \Rector\_Package_\Rector\_Category_\_Name_::class;
    }
}
