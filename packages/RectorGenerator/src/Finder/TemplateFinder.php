<?php

declare(strict_types=1);

namespace Rector\RectorGenerator\Finder;

use Rector\RectorGenerator\ValueObject\Configuration;
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
    public function find(Configuration $configuration): array
    {
        $finder = Finder::create()
            ->files()
            ->exclude('Fixture/')
            ->exclude('Source/')
            ->notName('*Test.php.inc')
            ->in(self::TEMPLATES_DIRECTORY);

        $smartFileInfos = $this->finderSanitizer->sanitize($finder);
        $smartFileInfos[] = $this->createFixtureSmartFileInfo($configuration->isPhpSnippet());

        if ($configuration->getExtraFileContent()) {
            $smartFileInfos[] = new SmartFileInfo(
                __DIR__ . '/../../templates/packages/_Package_/tests/Rector/_Category_/_Name_/Source/extra_file.php.inc'
            );
            $smartFileInfos[] = new SmartFileInfo(
                __DIR__ . '/../../templates/packages/_Package_/tests/Rector/_Category_/_Name_/_Name_ExtraTest.php.inc'
            );
        } else {
            $smartFileInfos[] = new SmartFileInfo(
                __DIR__ . '/../../templates/packages/_Package_/tests/Rector/_Category_/_Name_/_Name_Test.php.inc'
            );
        }

        return $smartFileInfos;
    }

    private function createFixtureSmartFileInfo(bool $isPhpSnippet): SmartFileInfo
    {
        if ($isPhpSnippet) {
            return new SmartFileInfo(
                __DIR__ . '/../../templates/packages/_Package_/tests/Rector/_Category_/_Name_/Fixture/fixture.php.inc'
            );
        }

        // is html snippet
        return new SmartFileInfo(
            __DIR__ . '/../../templates/packages/_Package_/tests/Rector/_Category_/_Name_/Fixture/html_fixture.php.inc'
        );
    }
}
