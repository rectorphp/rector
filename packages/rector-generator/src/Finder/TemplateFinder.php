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
        $filePaths = [];

        if ($configuration->getRuleConfiguration()) {
            $filePaths[] = __DIR__ . '/../../templates/rules/_package_/src/Rector/_Category_/_Name_WithConfiguration_.php.inc';
            $filePaths[] = __DIR__ . '/../../templates/rules/_package_/tests/Rector/_Category_/_Name_/Configured_Name_Test.php.inc';
        } else {
            $filePaths[] = __DIR__ . '/../../templates/rules/_package_/src/Rector/_Category_/_Name_.php.inc';
            $filePaths[] = __DIR__ . '/../../templates/rules/_package_/tests/Rector/_Category_/_Name_/_Name_Test.php.inc';
        }

        $filePaths[] = $this->resolveFixtureFilePath($configuration->isPhpSnippet());

        if ($configuration->getExtraFileContent()) {
            $filePaths[] = __DIR__ . '/../../templates/rules/_package_/tests/Rector/_Category_/_Name_/Source/extra_file.php.inc';
            $filePaths[] = __DIR__ . '/../../templates/rules/_package_/tests/Rector/_Category_/_Name_/_Name_ExtraTest.php.inc';
        } else {
            $filePaths[] = __DIR__ . '/../../templates/rules/_package_/tests/Rector/_Category_/_Name_/_Name_Test.php.inc';
        }

        return $this->finderSanitizer->sanitize($filePaths);
    }

    private function resolveFixtureFilePath(bool $isPhpSnippet): string
    {
        if ($isPhpSnippet) {
            return __DIR__ . '/../../templates/rules/_package_/tests/Rector/_Category_/_Name_/Fixture/fixture.php.inc';
        }

        // is html snippet
        return __DIR__ . '/../../templates/rules/_package_/tests/Rector/_Category_/_Name_/Fixture/html_fixture.php.inc';
    }
}
