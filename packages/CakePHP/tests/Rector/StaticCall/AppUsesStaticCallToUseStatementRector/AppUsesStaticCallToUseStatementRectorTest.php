<?php

declare(strict_types=1);

namespace Rector\CakePHP\Tests\Rector\StaticCall\AppUsesStaticCallToUseStatementRector;

use Iterator;
use Rector\CakePHP\Rector\StaticCall\AppUsesStaticCallToUseStatementRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class AppUsesStaticCallToUseStatementRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return AppUsesStaticCallToUseStatementRector::class;
    }
}
