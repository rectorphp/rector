<?php

declare(strict_types=1);

namespace Rector\Core\Tests\PhpParser\Printer;

use Nette\Utils\FileSystem;
use Rector\Core\HttpKernel\RectorKernel;
use Rector\Core\PhpParser\Printer\FormatPerservingPrinter;
use Symplify\PackageBuilder\Tests\AbstractKernelTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class FormatPerservingPrinterTest extends AbstractKernelTestCase
{
    /**
     * @var int
     */
    private const EXPECTED_FILEMOD = 0755;

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

        chmod(__DIR__ . '/Fixture/file.php', self::EXPECTED_FILEMOD);

        $fileInfo = new SmartFileInfo(__DIR__ . '/Fixture/file.php');

        $this->formatPerservingPrinter->printToFile($fileInfo, [], [], []);

        $this->assertSame(self::EXPECTED_FILEMOD, fileperms(__DIR__ . '/Fixture/file.php') & 0777);
    }
}
