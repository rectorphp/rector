<?php

declare(strict_types=1);

namespace Rector\Phalcon\Tests\Rector\Assign\FlashWithCssClassesToExtraCallRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Phalcon\Rector\Assign\FlashWithCssClassesToExtraCallRector;

final class FlashWithCssClassesToExtraCallRectorTest extends AbstractRectorTestCase
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
        return FlashWithCssClassesToExtraCallRector::class;
    }
}
