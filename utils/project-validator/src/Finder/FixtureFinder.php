<?php

declare(strict_types=1);

namespace Rector\Utils\ProjectValidator\Finder;

use Symfony\Component\Finder\Finder;
use Symplify\SmartFileSystem\Finder\FinderSanitizer;
use Symplify\SmartFileSystem\SmartFileInfo;

final class FixtureFinder
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
    public function findFixtureFileInfos(): array
    {
        $finder = new Finder();
        $finder = $finder->files()
            ->name('#\.php\.inc$#')
            ->notName('#empty_file\.php\.inc$#')
            ->path('#/Fixture/#')
            ->notPath('#/blade-template/#')
            ->notPath('#bootstrap_names\.php\.inc#')
            ->notPath('#trait_name\.php\.inc#')
            ->notName('#_\.php\.inc$#')
            ->in(__DIR__ . '/../../../../tests')
            ->in(__DIR__ . '/../../../../packages')
            ->in(__DIR__ . '/../../../../rules/architecture')
            ->in(__DIR__ . '/../../../../rules/autodiscovery')
            ->in(__DIR__ . '/../../../../rules/cakephp')
            ->in(__DIR__ . '/../../../../rules/carbon')
            ->in(__DIR__ . '/../../../../rules/code-quality')
            ->in(__DIR__ . '/../../../../rules/coding-style');

        return $this->finderSanitizer->sanitize($finder);
    }
}
