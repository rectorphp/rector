<?php

declare(strict_types=1);

namespace Rector\Tests\PhpParser\Printer;

use Nette\Utils\FileSystem;
use Rector\HttpKernel\RectorKernel;
use Rector\PhpParser\Printer\FormatPerservingPrinter;
use Symplify\PackageBuilder\Tests\AbstractKernelTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class FormatPerservingPrinterTest extends AbstractKernelTestCase
{
    /**
     * @var FormatPerservingPrinter
     */
    private $formatPerservingPrinter;

    protected function setUp(): void
    {
        $this->bootKernel(RectorKernel::class);
        $this->formatPerservingPrinter = self::$container->get(FormatPerservingPrinter::class);
    }

    protected function tearDown(): void
    {
        FileSystem::delete(__DIR__ . '/Fixture');
    }

    public function testFileModeIsPreserved(): void
    {
        mkdir(__DIR__ . '/Fixture');
        touch(__DIR__ . '/Fixture/file.php');
        $expectedFilemod = 0755;
        chmod(__DIR__ . '/Fixture/file.php', $expectedFilemod);

        $fileInfo = new SmartFileInfo(__DIR__ . '/Fixture/file.php');

        $this->formatPerservingPrinter->printToFile($fileInfo, [], [], []);

        $this->assertSame($expectedFilemod, fileperms(__DIR__ . '/Fixture/file.php') & 0777);
    }
}
