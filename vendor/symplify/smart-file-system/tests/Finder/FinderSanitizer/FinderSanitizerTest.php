<?php

declare (strict_types=1);
namespace RectorPrefix20210510\Symplify\SmartFileSystem\Tests\Finder\FinderSanitizer;

use RectorPrefix20210510\Nette\Utils\Finder as NetteFinder;
use RectorPrefix20210510\Nette\Utils\Strings;
use RectorPrefix20210510\PHPUnit\Framework\TestCase;
use SplFileInfo;
use RectorPrefix20210510\Symfony\Component\Finder\Finder as SymfonyFinder;
use RectorPrefix20210510\Symfony\Component\Finder\SplFileInfo as SymfonySplFileInfo;
use RectorPrefix20210510\Symplify\SmartFileSystem\Finder\FinderSanitizer;
use Symplify\SmartFileSystem\SmartFileInfo;
final class FinderSanitizerTest extends TestCase
{
    /**
     * @var FinderSanitizer
     */
    private $finderSanitizer;
    protected function setUp() : void
    {
        $this->finderSanitizer = new FinderSanitizer();
    }
    public function testValidTypes() : void
    {
        $files = [new SplFileInfo(__DIR__ . '/Source/MissingFile.php')];
        $sanitizedFiles = $this->finderSanitizer->sanitize($files);
        $this->assertCount(0, $sanitizedFiles);
    }
    public function testSymfonyFinder() : void
    {
        $symfonyFinder = SymfonyFinder::create()->files()->in(__DIR__ . '/Source');
        $fileInfos = \iterator_to_array($symfonyFinder->getIterator());
        $this->assertCount(2, $fileInfos);
        $files = $this->finderSanitizer->sanitize($symfonyFinder);
        $this->assertCount(2, $files);
        $this->assertFilesEqualFixtureFiles($files[0], $files[1]);
    }
    public function testNetteFinder() : void
    {
        $netteFinder = NetteFinder::findFiles('*')->from(__DIR__ . '/Source');
        $fileInfos = \iterator_to_array($netteFinder->getIterator());
        $this->assertCount(2, $fileInfos);
        $files = $this->finderSanitizer->sanitize($netteFinder);
        $this->assertCount(2, $files);
        $this->assertFilesEqualFixtureFiles($files[0], $files[1]);
    }
    /**
     * On different OS the order of the two files can differ, only symfony finder would have a sort function, nette
     * finder does not. so we test if the correct files are there but ignore the order.
     */
    private function assertFilesEqualFixtureFiles(SmartFileInfo $firstSmartFileInfo, SmartFileInfo $secondSmartFileInfo) : void
    {
        $this->assertFileIsFromFixtureDirAndHasCorrectClass($firstSmartFileInfo);
        $this->assertFileIsFromFixtureDirAndHasCorrectClass($secondSmartFileInfo);
        // order agnostic file check
        $this->assertTrue(Strings::endsWith($firstSmartFileInfo->getRelativeFilePath(), 'NestedDirectory/FileWithClass.php') && Strings::endsWith($secondSmartFileInfo->getRelativeFilePath(), 'NestedDirectory/EmptyFile.php') || Strings::endsWith($firstSmartFileInfo->getRelativeFilePath(), 'NestedDirectory/EmptyFile.php') && Strings::endsWith($secondSmartFileInfo->getRelativeFilePath(), 'NestedDirectory/FileWithClass.php'));
    }
    private function assertFileIsFromFixtureDirAndHasCorrectClass(SmartFileInfo $smartFileInfo) : void
    {
        $this->assertInstanceOf(SymfonySplFileInfo::class, $smartFileInfo);
        $this->assertStringEndsWith('NestedDirectory', $smartFileInfo->getRelativeDirectoryPath());
    }
}
