<?php

declare(strict_types=1);

namespace Rector\Utils\RectorGenerator\Finder;

use Symfony\Component\Finder\Finder;
use Symplify\SmartFileSystem\Finder\FinderSanitizer;
use Symplify\SmartFileSystem\SmartFileInfo;

final class TemplateFinder
{
    /**
     * @var string
     */
    public const TEMPLATES_DIRECTORY = __DIR__ . '/../../templates';

    /**
     * @var string
     */
    private const TEMPLATES_FIXTURE_DIRECTORY = __DIR__ . '/../../templates/packages/_Package_/tests/Rector/_Category_/_Name_/Fixture';

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
    public function find(bool $isPhpSnippet): array
    {
        $finder = Finder::create()->files()
            ->in(self::TEMPLATES_DIRECTORY)
            ->exclude(self::TEMPLATES_FIXTURE_DIRECTORY);

        $smartFileInfos = $this->finderSanitizer->sanitize($finder);
        $smartFileInfos[] = $this->createFixtureSmartFileInfo($isPhpSnippet);

        return $smartFileInfos;
    }

    private function createFixtureSmartFileInfo(bool $isPhpSnippet): SmartFileInfo
    {
        if ($isPhpSnippet) {
            return new SmartFileInfo(self::TEMPLATES_FIXTURE_DIRECTORY . '/fixture.php.inc');
        }

        // is html snippet
        return new SmartFileInfo(self::TEMPLATES_FIXTURE_DIRECTORY . '/html_fixture.php.inc');
    }
}
