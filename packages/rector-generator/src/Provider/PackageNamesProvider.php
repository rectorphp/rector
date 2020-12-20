<?php

declare(strict_types=1);

namespace Rector\RectorGenerator\Provider;

use Rector\Core\Util\StaticRectorStrings;
use Symfony\Component\Finder\Finder;

final class PackageNamesProvider
{
    /**
     * @var Finder
     */
    private $finder;

    public function __construct()
    {
        $this->finder = new Finder();
    }

    /**
     * @return array<int, string>
     */
    public function provide(): array
    {
        $directoriesList = $this->finder
            ->directories()
            ->depth(0)
            ->in(__DIR__ . '/../../../../rules/')
            ->getIterator()
        ;

        $names = [];
        foreach ($directoriesList as $directory) {
            $names[] = StaticRectorStrings::dashesToCamelCase($directory->getFilename());
        }

        return $names;
    }
}
