<?php

declare(strict_types=1);

namespace Rector\Set;

use Symfony\Component\Finder\Finder;

final class SetProvider
{
    /**
     * @return string[]
     */
    public function provide(): array
    {
        $finder = Finder::create()->files()
            ->in(Set::SET_DIRECTORY);

        $sets = [];
        foreach ($finder->getIterator() as $fileInfo) {
            $sets[] = $fileInfo->getBasename('.' . $fileInfo->getExtension());
        }

        sort($sets);

        return array_unique($sets);
    }
}
