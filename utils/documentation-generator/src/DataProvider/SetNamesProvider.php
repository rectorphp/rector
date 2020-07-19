<?php

declare(strict_types=1);

namespace Rector\Utils\DocumentationGenerator\DataProvider;

use Rector\Core\Util\StaticRectorStrings;
use Rector\Core\ValueObject\SetDirectory;
use Symfony\Component\Finder\Finder;
use Symplify\SmartFileSystem\Finder\FinderSanitizer;
use Symplify\SmartFileSystem\SmartFileInfo;

final class SetNamesProvider
{
    /**
     * @var FinderSanitizer
     */
    private $finderSanitizer;

    public function __construct(FinderSanitizer $finderSanitizer)
    {
        $this->finderSanitizer = $finderSanitizer;
    }

    /**
     * @return array<string, string>
     */
    public function provide(): array
    {
        $setFileInfos = $this->findFileInfos();

        $constantNamesToSetNames = [];
        foreach ($setFileInfos as $setFileInfo) {
            $setName = $setFileInfo->getBasenameWithoutSuffix();

            $constantName = StaticRectorStrings::dashesToConstants($setName);
            $constantNamesToSetNames[$constantName] = $setName;
        }

        ksort($constantNamesToSetNames);

        return $constantNamesToSetNames;
    }

    /**
     * @return SmartFileInfo[]
     */
    private function findFileInfos(): array
    {
        $finder = new Finder();
        $finder->in(SetDirectory::SET_DIRECTORY)
            ->name('*.php')
            ->files();

        return $this->finderSanitizer->sanitize($finder);
    }
}
