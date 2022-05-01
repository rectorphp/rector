<?php

declare (strict_types=1);
namespace RectorPrefix20220501\Symplify\EasyTesting;

use RectorPrefix20220501\Nette\Utils\Strings;
use RectorPrefix20220501\Symplify\EasyTesting\ValueObject\IncorrectAndMissingSkips;
use RectorPrefix20220501\Symplify\EasyTesting\ValueObject\Prefix;
use RectorPrefix20220501\Symplify\EasyTesting\ValueObject\SplitLine;
use Symplify\SmartFileSystem\SmartFileInfo;
/**
 * @see \Symplify\EasyTesting\Tests\MissingSkipPrefixResolver\MissingSkipPrefixResolverTest
 */
final class MissplacedSkipPrefixResolver
{
    /**
     * @param SmartFileInfo[] $fixtureFileInfos
     */
    public function resolve(array $fixtureFileInfos) : \RectorPrefix20220501\Symplify\EasyTesting\ValueObject\IncorrectAndMissingSkips
    {
        $incorrectSkips = [];
        $missingSkips = [];
        foreach ($fixtureFileInfos as $fixtureFileInfo) {
            $hasNameSkipStart = $this->hasNameSkipStart($fixtureFileInfo);
            $fileContents = $fixtureFileInfo->getContents();
            $hasSplitLine = (bool) \RectorPrefix20220501\Nette\Utils\Strings::match($fileContents, \RectorPrefix20220501\Symplify\EasyTesting\ValueObject\SplitLine::SPLIT_LINE_REGEX);
            if ($hasNameSkipStart && $hasSplitLine) {
                $incorrectSkips[] = $fixtureFileInfo;
                continue;
            }
            if (!$hasNameSkipStart && !$hasSplitLine) {
                $missingSkips[] = $fixtureFileInfo;
            }
        }
        return new \RectorPrefix20220501\Symplify\EasyTesting\ValueObject\IncorrectAndMissingSkips($incorrectSkips, $missingSkips);
    }
    private function hasNameSkipStart(\Symplify\SmartFileSystem\SmartFileInfo $fixtureFileInfo) : bool
    {
        return (bool) \RectorPrefix20220501\Nette\Utils\Strings::match($fixtureFileInfo->getBasenameWithoutSuffix(), \RectorPrefix20220501\Symplify\EasyTesting\ValueObject\Prefix::SKIP_PREFIX_REGEX);
    }
}
