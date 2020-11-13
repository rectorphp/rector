<?php

declare(strict_types=1);

namespace Rector\Core\Tests\PhpParser\Printer\CommentRemover;

use Iterator;
use Rector\Core\HttpKernel\RectorKernel;
use Rector\Core\PhpParser\Printer\CommentRemover;
use Symplify\EasyTesting\DataProvider\StaticFixtureFinder;
use Symplify\EasyTesting\StaticFixtureSplitter;
use Symplify\PackageBuilder\Tests\AbstractKernelTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class CommentRemoverTest extends AbstractKernelTestCase
{
    /**
     * @var CommentRemover
     */
    private $commentRemover;

    protected function setUp(): void
    {
        $this->bootKernel(RectorKernel::class);

        $this->commentRemover = static::$container->get(CommentRemover::class);
    }

    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $smartFileInfo): void
    {
        $inputAndExpected = StaticFixtureSplitter::splitFileInfoToInputAndExpected($smartFileInfo);

        $removedContent = $this->commentRemover->remove($inputAndExpected->getInput());
        $this->assertSame(
            $inputAndExpected->getExpected(),
            $removedContent,
            $smartFileInfo->getRelativeFilePathFromCwd()
        );
    }

    public function provideData(): Iterator
    {
        return StaticFixtureFinder::yieldDirectory(__DIR__ . '/Fixture', '*.php.inc');
    }
}
