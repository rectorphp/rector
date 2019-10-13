<?php

declare(strict_types=1);

namespace Rector\Testing\PHPUnit;

use Nette\Utils\FileSystem;
use Nette\Utils\Strings;
use Symplify\PackageBuilder\FileSystem\SmartFileInfo;

final class FixtureSplitter
{
    /**
     * @var string
     */
    private const SPLIT_LINE = '#-----\n#';

    /**
     * @var string
     */
    private $tempPath;

    public function __construct(string $tempPath)
    {
        $this->tempPath = $tempPath;
    }

    /**
     * @return string[]
     */
    public function splitContentToOriginalFileAndExpectedFile(
        SmartFileInfo $smartFileInfo,
        bool $autoloadTestFixture
    ): array {
        if (Strings::match($smartFileInfo->getContents(), self::SPLIT_LINE)) {
            // original → expected
            [$originalContent, $expectedContent] = Strings::split($smartFileInfo->getContents(), self::SPLIT_LINE);
        } else {
            // no changes
            $originalContent = $smartFileInfo->getContents();
            $expectedContent = $originalContent;
        }

        $originalFile = $this->createTemporaryPathWithPrefix($smartFileInfo, 'original');
        $expectedFile = $this->createTemporaryPathWithPrefix($smartFileInfo, 'expected');

        FileSystem::write($originalFile, $originalContent);
        FileSystem::write($expectedFile, $expectedContent);

        // file needs to be autoload so PHPStan can analyze
        if ($autoloadTestFixture) {
            require_once $originalFile;
        }

        return [$originalFile, $expectedFile];
    }

    private function createTemporaryPathWithPrefix(SmartFileInfo $smartFileInfo, string $prefix): string
    {
        $hash = Strings::substring(md5($smartFileInfo->getRealPath()), 0, 5);

        return sprintf($this->tempPath . '/%s_%s_%s', $prefix, $hash, $smartFileInfo->getBasename('.inc'));
    }
}
