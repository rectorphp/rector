<?php

declare(strict_types=1);

namespace Rector\DowngradePhp73\Tests\Rector\FuncCall\SetCookieOptionsArrayToArguments;

use Iterator;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\DowngradePhp73\Rector\FuncCall\SetCookieOptionsArrayToArguments;
use Rector\DowngradePhp73\Rector\List_\DowngradeListReferenceAssignmentRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class SetCookieOptionsArrayToArgumentsTest extends AbstractRectorTestCase
{
    /**
     * @requires PHP 7.3
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fileInfo): void
    {
        $this->doTestFileInfo($fileInfo);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return SetCookieOptionsArrayToArguments::class;
    }
}
