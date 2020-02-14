<?php

declare(strict_types=1);

namespace Rector\_Package_\Tests\Rector\_Category_\_Name_;

use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;

final class _Name_Test extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);
    }

    public function provideData(): \Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return \Rector\_Package_\Rector\_Category_\_Name_::class;
    }
}
