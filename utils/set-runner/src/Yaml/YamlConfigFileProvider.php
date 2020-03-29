<?php

declare(strict_types=1);

namespace Rector\Utils\SetRunner\Yaml;

use Symfony\Component\Finder\Finder;
use Symplify\SmartFileSystem\Finder\FinderSanitizer;
use Symplify\SmartFileSystem\SmartFileInfo;

final class YamlConfigFileProvider
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
     * @return SmartFileInfo[]
     */
    public function provider(): array
    {
        $finder = (new Finder())->name('*.yaml')
            ->in(__DIR__ . '/../../../../config')
            ->sortByName()
            ->files();

        return $this->finderSanitizer->sanitize($finder);
    }
}
