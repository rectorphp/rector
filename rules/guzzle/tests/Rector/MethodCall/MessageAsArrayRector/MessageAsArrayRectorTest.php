<?php

declare(strict_types=1);

namespace Rector\Guzzle\Tests\Rector\MethodCall\MessageAsArrayRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Guzzle\Rector\MethodCall\MessageAsArrayRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class MessageAsArrayRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $file): void
    {
        $this->doTestFileInfo($file);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return MessageAsArrayRector::class;
    }
}
