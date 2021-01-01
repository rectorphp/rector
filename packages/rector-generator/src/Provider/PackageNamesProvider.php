<?php

declare(strict_types=1);

namespace Rector\RectorGenerator\Provider;

use Rector\Core\Util\StaticRectorStrings;
use SplFileInfo;
use Symfony\Component\Finder\Finder;

/**
 * @see \Rector\RectorGenerator\Tests\Provider\PackageNamesProviderTest
 */
final class PackageNamesProvider
{
    /**
     * @return string[]
     */
    public function provide(): array
    {
        $finder = new Finder();
        $finder = $finder->directories()
            ->depth(0)
            ->in(__DIR__ . '/../../../../rules')
            ->sortByName();

        $fileInfos = iterator_to_array($finder->getIterator());

        $packageNames = [];

        foreach ($fileInfos as $fileInfo) {
            /** @var SplFileInfo $fileInfo */
            $packageNames[] = StaticRectorStrings::dashesToCamelCase($fileInfo->getFilename());
        }

        return $packageNames;
    }
}
