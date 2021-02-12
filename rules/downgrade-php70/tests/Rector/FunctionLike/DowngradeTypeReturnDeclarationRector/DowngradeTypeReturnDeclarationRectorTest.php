<?php

declare(strict_types=1);

namespace Rector\DowngradePhp70\Tests\Rector\FunctionLike\DowngradeTypeReturnDeclarationRector;

use Iterator;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class DowngradeTypeReturnDeclarationRectorTest extends AbstractRectorTestCase
{
    /**
     * @requires PHP 7.0
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

    protected function provideConfigFilePath(): string
    {
        return __DIR__ . '/config/php_70.php';
    }
}
