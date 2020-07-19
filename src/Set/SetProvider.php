<?php

declare(strict_types=1);

namespace Rector\Core\Set;

use Rector\Core\ValueObject\SetDirectory;
use Symfony\Component\Finder\Finder;

final class SetProvider
{
    /**
     * @return string[]
     */
    public function provide(): array
    {
        $finder = Finder::create()->files()
            ->in(SetDirectory::SET_DIRECTORY);

        $sets = [];
        foreach ($finder->getIterator() as $fileInfo) {
            $sets[] = $fileInfo->getBasename('.' . $fileInfo->getExtension());
        }

        sort($sets);

        return array_unique($sets);
    }
}
