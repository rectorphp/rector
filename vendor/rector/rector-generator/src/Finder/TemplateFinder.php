<?php

declare (strict_types=1);
namespace Rector\RectorGenerator\Finder;

use Rector\RectorGenerator\ValueObject\RectorRecipe;
use RectorPrefix20211231\Symplify\SmartFileSystem\FileSystemGuard;
use RectorPrefix20211231\Symplify\SmartFileSystem\Finder\FinderSanitizer;
use Symplify\SmartFileSystem\SmartFileInfo;
final class TemplateFinder
{
    /**
     * @var string
     */
    public const TEMPLATES_DIRECTORY = __DIR__ . '/../../templates';
    /**
     * @readonly
     * @var \Symplify\SmartFileSystem\Finder\FinderSanitizer
     */
    private $finderSanitizer;
    /**
     * @readonly
     * @var \Symplify\SmartFileSystem\FileSystemGuard
     */
    private $fileSystemGuard;
    public function __construct(\RectorPrefix20211231\Symplify\SmartFileSystem\Finder\FinderSanitizer $finderSanitizer, \RectorPrefix20211231\Symplify\SmartFileSystem\FileSystemGuard $fileSystemGuard)
    {
        $this->finderSanitizer = $finderSanitizer;
        $this->fileSystemGuard = $fileSystemGuard;
    }
    /**
     * @return SmartFileInfo[]
     */
    public function find(\Rector\RectorGenerator\ValueObject\RectorRecipe $rectorRecipe) : array
    {
        $filePaths = [];
        $filePaths = $this->addRuleAndTestCase($rectorRecipe, $filePaths);
        $filePaths[] = __DIR__ . '/../../templates/rules-tests/__Package__/Rector/__Category__/__Name__/Fixture/some_class.php.inc';
        $this->ensureFilePathsExists($filePaths);
        return $this->finderSanitizer->sanitize($filePaths);
    }
    /**
     * @param string[] $filePaths
     * @return string[]
     *
     * @note the ".inc" suffix is needed, so PHPUnit doens't load it as a test case
     */
    private function addRuleAndTestCase(\Rector\RectorGenerator\ValueObject\RectorRecipe $rectorRecipe, array $filePaths) : array
    {
        if ($rectorRecipe->getConfiguration() !== []) {
            $filePaths[] = __DIR__ . '/../../templates/rules-tests/__Package__/Rector/__Category__/__Name__/config/__Configuredconfigured_rule.php';
        } else {
            $filePaths[] = __DIR__ . '/../../templates/rules-tests/__Package__/Rector/__Category__/__Name__/config/configured_rule.php';
        }
        $filePaths[] = __DIR__ . '/../../templates/rules-tests/__Package__/Rector/__Category__/__Name__/__Name__Test.php.inc';
        if ($rectorRecipe->getConfiguration() !== []) {
            $filePaths[] = __DIR__ . '/../../templates/rules/__Package__/Rector/__Category__/__Configured__Name__.php';
        } else {
            $filePaths[] = __DIR__ . '/../../templates/rules/__Package__/Rector/__Category__/__Name__.php';
        }
        return $filePaths;
    }
    /**
     * @param string[] $filePaths
     */
    private function ensureFilePathsExists(array $filePaths) : void
    {
        foreach ($filePaths as $filePath) {
            $this->fileSystemGuard->ensureFileExists($filePath, __METHOD__);
        }
    }
}
