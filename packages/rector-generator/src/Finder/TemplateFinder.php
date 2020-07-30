<?php

declare(strict_types=1);

namespace Rector\RectorGenerator\Finder;

use Rector\RectorGenerator\ValueObject\Configuration;
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
    public function find(Configuration $configuration): array
    {
        $filePaths = [];

        if ($configuration->getExtraFileContent()) {
            $filePaths[] = __DIR__ . '/../../templates/rules/__package__/tests/Rector/__Category__/__Name__/Source/extra_file.php.inc';
        }

        $filePaths = $this->addRuleAndTestCase($configuration, $filePaths);

        /** @var string[] $filePaths */
        $filePaths[] = $this->resolveFixtureFilePath($configuration->isPhpSnippet());

        $this->ensureFilePathsExists($filePaths);

        return $this->finderSanitizer->sanitize($filePaths);
    }

    private function resolveFixtureFilePath(bool $isPhpSnippet): string
    {
        if ($isPhpSnippet) {
            return __DIR__ . '/../../templates/rules/__package__/tests/Rector/__Category__/__Name__/Fixture/fixture.php.inc';
        }

        // is html snippet
        return __DIR__ . '/../../templates/rules/__package__/tests/Rector/__Category__/__Name__/Fixture/html_fixture.php.inc';
    }

    /**
     * @param string[] $filePaths
     * @return string[]
     *
     * @note the ".inc" suffix is needed, so PHPUnit doens't load it as a test case;
     * unfortunately we haven't found a way to preven it
     */
    private function addRuleAndTestCase(Configuration $configuration, array $filePaths): array
    {
        if ($configuration->getRuleConfiguration() !== []) {
            $filePaths[] = __DIR__ . '/../../templates/rules/__package__/src/Rector/__Category__/__Configured__Name__.php';

            if ($configuration->getExtraFileContent()) {
                $filePaths[] = __DIR__ . '/../../templates/rules/__package__/tests/Rector/__Category__/__Name__/__Configured__Extra__Name__Test.php.inc';
            } else {
                $filePaths[] = __DIR__ . '/../../templates/rules/__package__/tests/Rector/__Category__/__Name__/__Configured__Name__Test.php.inc';
            }

            return $filePaths;
        }

        if ($configuration->getExtraFileContent()) {
            $filePaths[] = __DIR__ . '/../../templates/rules/__package__/tests/Rector/__Category__/__Name__/__Extra__Name__Test.php.inc';
        } else {
            $filePaths[] = __DIR__ . '/../../templates/rules/__package__/tests/Rector/__Category__/__Name__/__Name__Test.php.inc';
        }

        $filePaths[] = __DIR__ . '/../../templates/rules/__package__/src/Rector/__Category__/__Name__.php';

        return $filePaths;
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
