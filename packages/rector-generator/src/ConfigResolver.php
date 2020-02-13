<?php

declare(strict_types=1);

namespace Rector\RectorGenerator;

use Nette\Utils\Strings;
use Rector\Core\Set\Set;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

final class ConfigResolver
{
    public function resolveSetConfig(string $set): ?string
    {
        if ($set === '') {
            return null;
        }

        $fileInfos = $this->getSetFileInfos($set);

        if (count($fileInfos) === 0) {
            // assume new one is created
            $match = Strings::match($set, '#\/(?<name>[a-zA-Z_-]+])#');
            if (isset($match['name'])) {
                return Set::SET_DIRECTORY . '/' . $match['name'] . '/' . $set;
            }

            return null;
        }

        /** @var SplFileInfo $foundSetConfigFileInfo */
        $foundSetConfigFileInfo = array_pop($fileInfos);

        return $foundSetConfigFileInfo->getRealPath();
    }

    /**
     * @return SplFileInfo[]
     */
    private function getSetFileInfos(string $set): array
    {
        $fileSet = sprintf('#^%s(\.yaml)?$#', $set);
        $finder = Finder::create()->files()
            ->in(Set::SET_DIRECTORY)
            ->name($fileSet);

        return iterator_to_array($finder->getIterator());
    }
}
