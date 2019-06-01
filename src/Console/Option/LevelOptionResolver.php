<?php declare(strict_types=1);

namespace Rector\Console\Option;

use Nette\Utils\ObjectHelpers;
use Nette\Utils\Strings;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symplify\PackageBuilder\Configuration\ConfigFileFinder;
use Symplify\PackageBuilder\Exception\Configuration\LevelNotFoundException;

final class LevelOptionResolver
{
    /**
     * @var string
     */
    private $keyName;

    /**
     * @var string[]
     */
    private $optionNames = [];

    /**
     * @param string[] $optionNames
     */
    public function __construct(array $optionNames = ['--level', '-l'], string $keyName = 'level')
    {
        $this->optionNames = $optionNames;
        $this->keyName = $keyName;
    }

    public function detectFromInputAndDirectory(InputInterface $input, string $configDirectory): ?string
    {
        $levelName = ConfigFileFinder::getOptionValue($input, $this->optionNames);
        if ($levelName === null) {
            return null;
        }

        $nearestMatches = $this->findNearestMatchingFiles($configDirectory, $levelName);
        if (count($nearestMatches) === 0) {
            $this->reportLevelNotFound($configDirectory, $levelName);
        }

        /** @var SplFileInfo $nearestMatch */
        $nearestMatch = array_shift($nearestMatches);

        return $nearestMatch->getRealPath();
    }

    private function reportLevelNotFound(string $configDirectory, string $levelName): void
    {
        $allLevels = $this->findAllLevelsInDirectory($configDirectory);

        $suggestedLevel = ObjectHelpers::getSuggestion($allLevels, $levelName);

        $hasLevelVersion = (bool) Strings::match($levelName, '#[\d]#');

        // split versioned and unversioned configs
        $versionedLevels = [];
        $unversionedLevels = [];
        foreach ($allLevels as $level) {
            $match = Strings::match($level, '#^[A-Za-z\-]+#');
            if ($match) {
                $levelWithoutVersion = rtrim($match[0], '-');
                if ($levelWithoutVersion !== $level) {
                    $versionedLevels[$levelWithoutVersion][] = $level;
                    continue;
                }
            }

            $unversionedLevels[] = $level;
        }

        $levelsListInString = $this->createLevelListInString($hasLevelVersion, $unversionedLevels, $versionedLevels);

        $levelNotFoundMessage = sprintf(
            '%s "%s" was not found.%s%s',
            ucfirst($this->keyName),
            $levelName,
            PHP_EOL,
            $suggestedLevel ? sprintf('Did you mean "%s"?', $suggestedLevel) . PHP_EOL : 'Pick one of above.'
        );

        $pickOneOfMessage = sprintf('Pick "--%s" of:%s%s', $this->keyName, PHP_EOL . PHP_EOL, $levelsListInString);

        throw new LevelNotFoundException($levelNotFoundMessage . PHP_EOL . $pickOneOfMessage);
    }

    /**
     * @return string[]
     */
    private function findAllLevelsInDirectory(string $configDirectory): array
    {
        $finder = Finder::create()
            ->files()
            ->in($configDirectory);

        $levels = [];
        foreach ($finder->getIterator() as $fileInfo) {
            $levels[] = $fileInfo->getBasename('.' . $fileInfo->getExtension());
        }

        sort($levels);

        return array_unique($levels);
    }

    /**
     * @return SplFileInfo[]
     */
    private function findNearestMatchingFiles(string $configDirectory, string $levelName): array
    {
        $configFiles = Finder::create()
            ->files()
            ->in($configDirectory)
            ->getIterator();

        $nearestMatches = [];

        $levelName = Strings::lower($levelName);

        // the version must match, so 401 is not compatible with 40
        $levelVersion = $this->matchVersionInTheEnd($levelName);

        foreach ($configFiles as $configFile) {
            // only similar configs, not too far
            // this allows to match "Symfony.40" to "symfony40" config
            $distance = levenshtein($configFile->getFilenameWithoutExtension(), $levelName);
            if ($distance > 2) {
                continue;
            }

            if ($levelVersion) {
                $fileVersion = $this->matchVersionInTheEnd($configFile->getFilenameWithoutExtension());
                if ($levelVersion !== $fileVersion) {
                    // not a version match
                    continue;
                }
            }

            $nearestMatches[$distance] = $configFile;
        }

        ksort($nearestMatches);

        return $nearestMatches;
    }

    private function matchVersionInTheEnd(string $levelName): ?string
    {
        $match = Strings::match($levelName, '#(?<version>[\d\.]+$)#');
        if (! $match) {
            return null;
        }

        $version = $match['version'];
        return Strings::replace($version, '#\.#');
    }

    /**
     * @param string[] $unversionedLevels
     * @param string[][] $versionedLevels
     */
    private function createLevelListInString(
        bool $hasLevelVersion,
        array $unversionedLevels,
        array $versionedLevels
    ): string {
        $levelsListInString = '';

        if ($hasLevelVersion === false) {
            foreach ($unversionedLevels as $unversionedLevel) {
                $levelsListInString .= ' * ' . $unversionedLevel . PHP_EOL;
            }
        }

        if ($hasLevelVersion) {
            foreach ($versionedLevels as $groupName => $configName) {
                $levelsListInString .= ' * ' . $groupName . ': ' . implode(', ', $configName) . PHP_EOL;
            }
        }
        return $levelsListInString;
    }
}
