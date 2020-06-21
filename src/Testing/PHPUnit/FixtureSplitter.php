<?php

declare(strict_types=1);

namespace Rector\Core\Testing\PHPUnit;

use Nette\Utils\FileSystem;
use Nette\Utils\Strings;
use Rector\Core\Testing\ValueObject\SplitLine;
use Symplify\SmartFileSystem\SmartFileInfo;

final class FixtureSplitter
{
    /**
     * @var string
     */
    private $tempPath;

    public function __construct(string $tempPath)
    {
        $this->tempPath = $tempPath;
    }

    /**
     * @return SmartFileInfo[]
     */
    public function splitContentToOriginalFileAndExpectedFile(
        SmartFileInfo $smartFileInfo,
        bool $autoloadTestFixture
    ): array {
        [$originalContent, $expectedContent] = $this->resolveBeforeAfterFixtureContent($smartFileInfo);

        $originalFile = $this->createTemporaryPathWithPrefix($smartFileInfo, 'original');
        $expectedFile = $this->createTemporaryPathWithPrefix($smartFileInfo, 'expected');

        FileSystem::write($originalFile, $originalContent);
        FileSystem::write($expectedFile, $expectedContent);

        // file needs to be autoload so PHPStan can analyze
        if ($autoloadTestFixture && Strings::match($smartFileInfo->getFilename(), '#\.php(\.inc)?$#')) {
            require_once $originalFile;
        }

        $originalFileInfo = new SmartFileInfo($originalFile);
        $expectedFileInfo = new SmartFileInfo($expectedFile);

        return [$originalFileInfo, $expectedFileInfo];
    }

    public function createTemporaryPathWithPrefix(SmartFileInfo $smartFileInfo, string $prefix): string
    {
        // warning: if this hash is too short, the file can becom "identical"; took me 1 hour to find out
        $hash = Strings::substring(md5($smartFileInfo->getRealPath()), -15);

        return sprintf($this->tempPath . '/%s_%s_%s', $prefix, $hash, $smartFileInfo->getBasename('.inc'));
    }

    /**
     * @return string[]
     */
    private function resolveBeforeAfterFixtureContent(SmartFileInfo $smartFileInfo): array
    {
        if (Strings::match($smartFileInfo->getContents(), SplitLine::SPLIT_LINE)) {
            // original → expected
            [$originalContent, $expectedContent] = Strings::split($smartFileInfo->getContents(), SplitLine::SPLIT_LINE);
        } else {
            // no changes
            $originalContent = $smartFileInfo->getContents();
            $expectedContent = $originalContent;
        }

        return [$originalContent, $expectedContent];
    }
}
