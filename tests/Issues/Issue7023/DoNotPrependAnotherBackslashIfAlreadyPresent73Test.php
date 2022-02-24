<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Issues\Issue7023;

use Iterator;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * @see https://github.com/rectorphp/rector/issues/7023
 */
final class DoNotPrependAnotherBackslashIfAlreadyPresent73Test extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fileInfo): void
    {
        $this->doTestFileInfo($fileInfo);
    }

    /**
     * @return Iterator<SmartFileInfo>
     */
    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture/7.3');
    }

    public function provideConfigFilePath(): string
    {
        return __DIR__ . '/config/configured_rule_73.php';
    }
}
