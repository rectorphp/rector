<?php

declare(strict_types=1);

namespace Rector\Core\Tests\PhpParser\Printer;

use Rector\Core\PhpParser\Printer\FormatPerservingPrinter;
use Rector\Testing\PHPUnit\AbstractTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;
use Symplify\SmartFileSystem\SmartFileSystem;

final class FormatPerservingPrinterTest extends AbstractTestCase
{
    /**
     * @var int
     */
    private const EXPECTED_FILEMOD = 0755;

    private FormatPerservingPrinter $formatPerservingPrinter;

    private SmartFileSystem $smartFileSystem;

    protected function setUp(): void
    {
        $this->boot();
        $this->formatPerservingPrinter = $this->getService(FormatPerservingPrinter::class);
        $this->smartFileSystem = $this->getService(SmartFileSystem::class);
    }

    protected function tearDown(): void
    {
        $this->smartFileSystem->remove(__DIR__ . '/Fixture');
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
