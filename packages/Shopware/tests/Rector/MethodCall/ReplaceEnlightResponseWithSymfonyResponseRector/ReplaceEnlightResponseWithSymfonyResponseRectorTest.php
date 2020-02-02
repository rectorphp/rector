<?php

declare(strict_types=1);

namespace Rector\Shopware\Tests\Rector\MethodCall\ReplaceEnlightResponseWithSymfonyResponseRector;

use Iterator;
use Rector\Shopware\Rector\MethodCall\ReplaceEnlightResponseWithSymfonyResponseRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ReplaceEnlightResponseWithSymfonyResponseRectorTest extends AbstractRectorTestCase
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
        return ReplaceEnlightResponseWithSymfonyResponseRector::class;
    }
}
