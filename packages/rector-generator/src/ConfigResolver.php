<?php

declare(strict_types=1);

namespace Rector\RectorGenerator;

use Nette\Utils\Strings;
use Rector\Core\Configuration\Set\SetResolver;
use Rector\Core\ValueObject\SetDirectory;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

final class ConfigResolver
{
    /**
     * @deprecated Use
     * @see SetResolver instead
     */
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
                return SetDirectory::SET_DIRECTORY . '/' . $match['name'] . '/' . $set;
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
        $fileSet = sprintf('#^%s\.php$#', $set);
        $finder = Finder::create()->files()
            ->in(SetDirectory::SET_DIRECTORY)
            ->name($fileSet);

        return iterator_to_array($finder->getIterator());
    }
}
