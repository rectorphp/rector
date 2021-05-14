<?php

declare (strict_types=1);
namespace Rector\NetteToSymfony\Rector\Class_;

use RectorPrefix20210514\Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\Core\Rector\AbstractRector;
use Rector\FileSystemRector\ValueObject\AddedFileWithContent;
use Rector\PSR4\FileInfoAnalyzer\FileInfoDeletionAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\NetteToSymfony\Tests\Rector\Class_\RenameTesterTestToPHPUnitToTestFileRector\RenameTesterTestToPHPUnitToTestFileRectorTest
 */
final class RenameTesterTestToPHPUnitToTestFileRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var string
     * @see https://regex101.com/r/ioamnE/1
     */
    private const PHP_SUFFIX_REGEX = '#\\.php$#';
    /**
     * @var string
     * @see https://regex101.com/r/cOMZIj/1
     */
    private const PHPT_SUFFIX_REGEX = '#\\.phpt$#';
    /**
     * @var FileInfoDeletionAnalyzer
     */
    private $fileInfoDeletionAnalyzer;
    public function __construct(\Rector\PSR4\FileInfoAnalyzer\FileInfoDeletionAnalyzer $fileInfoDeletionAnalyzer)
    {
        $this->fileInfoDeletionAnalyzer = $fileInfoDeletionAnalyzer;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Rename "*.phpt" file to "*Test.php" file', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
// tests/SomeTestCase.phpt
CODE_SAMPLE
, <<<'CODE_SAMPLE'
// tests/SomeTestCase.php
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $smartFileInfo = $this->file->getSmartFileInfo();
        $oldRealPath = $smartFileInfo->getRealPath();
        if (!\RectorPrefix20210514\Nette\Utils\Strings::endsWith($oldRealPath, '.phpt')) {
            return null;
        }
        $newRealPath = $this->createNewRealPath($oldRealPath);
        if ($newRealPath === $oldRealPath) {
            return null;
        }
        $this->removedAndAddedFilesCollector->removeFile($smartFileInfo);
        $addedFileWithContent = new \Rector\FileSystemRector\ValueObject\AddedFileWithContent($newRealPath, $smartFileInfo->getContents());
        $this->removedAndAddedFilesCollector->addAddedFile($addedFileWithContent);
        return null;
    }
    private function createNewRealPath(string $oldRealPath) : string
    {
        // file suffix
        $newRealPath = \RectorPrefix20210514\Nette\Utils\Strings::replace($oldRealPath, self::PHPT_SUFFIX_REGEX, '.php');
        // cleanup tests prefix
        $newRealPath = $this->fileInfoDeletionAnalyzer->clearNameFromTestingPrefix($newRealPath);
        // Test suffix
        if (!\RectorPrefix20210514\Nette\Utils\Strings::endsWith($newRealPath, 'Test.php')) {
            return \RectorPrefix20210514\Nette\Utils\Strings::replace($newRealPath, self::PHP_SUFFIX_REGEX, 'Test.php');
        }
        return $newRealPath;
    }
}
