<?php

declare(strict_types=1);

namespace Rector\RectorGenerator\Finder;

use Rector\RectorGenerator\ValueObject\RectorRecipe;
use Symplify\SmartFileSystem\FileSystemGuard;
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

    /**
     * @var FileSystemGuard
     */
    private $fileSystemGuard;

    public function __construct(FinderSanitizer $finderSanitizer, FileSystemGuard $fileSystemGuard)
    {
        $this->finderSanitizer = $finderSanitizer;
        $this->fileSystemGuard = $fileSystemGuard;
    }

    /**
     * @return SmartFileInfo[]
     */
    public function find(RectorRecipe $rectorRecipe): array
    {
        $filePaths = [];

        if ($rectorRecipe->getExtraFileContent()) {
            $filePaths[] = __DIR__ . '/../../templates/rules/__package__/tests/Rector/__Category__/__Name__/Source/extra_file.php.inc';
        }

        $filePaths = $this->addRuleAndTestCase($rectorRecipe, $filePaths);
        $filePaths[] = $this->resolveFixtureFilePath();

        $this->ensureFilePathsExists($filePaths);

        return $this->finderSanitizer->sanitize($filePaths);
    }

    /**
     * @param string[] $filePaths
     * @return string[]
     *
     * @note the ".inc" suffix is needed, so PHPUnit doens't load it as a test case;
     * unfortunately we haven't found a way to preven it
     */
    private function addRuleAndTestCase(RectorRecipe $rectorRecipe, array $filePaths): array
    {
        if ($rectorRecipe->getConfiguration() !== []) {
            $filePaths[] = __DIR__ . '/../../templates/rules/__package__/src/Rector/__Category__/__Configured__Name__.php';

            if ($rectorRecipe->getExtraFileContent()) {
                $filePaths[] = __DIR__ . '/../../templates/rules/__package__/tests/Rector/__Category__/__Name__/__Configured__Extra__Name__Test.php.inc';
            } else {
                $filePaths[] = __DIR__ . '/../../templates/rules/__package__/tests/Rector/__Category__/__Name__/__Configured__Name__Test.php.inc';
            }

            return $filePaths;
        }

        if ($rectorRecipe->getExtraFileContent()) {
            $filePaths[] = __DIR__ . '/../../templates/rules/__package__/tests/Rector/__Category__/__Name__/__Extra__Name__Test.php.inc';
        } else {
            $filePaths[] = __DIR__ . '/../../templates/rules/__package__/tests/Rector/__Category__/__Name__/__Name__Test.php.inc';
        }

        $filePaths[] = __DIR__ . '/../../templates/rules/__package__/src/Rector/__Category__/__Name__.php';

        return $filePaths;
    }

    private function resolveFixtureFilePath(): string
    {
        return __DIR__ . '/../../templates/rules/__package__/tests/Rector/__Category__/__Name__/Fixture/some_class.php.inc';
    }

    /**
     * @param string[] $filePaths
     */
    private function ensureFilePathsExists(array $filePaths): void
    {
        foreach ($filePaths as $filePath) {
            $this->fileSystemGuard->ensureFileExists($filePath, __METHOD__);
        }
    }
}
