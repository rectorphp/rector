<?php

declare (strict_types=1);
namespace Rector\Restoration\Rector\ClassLike;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassLike;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\Application\File;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Restoration\Rector\ClassLike\UpdateFileNameByClassNameFileSystemRector\UpdateFileNameByClassNameFileSystemRectorTest
 */
final class UpdateFileNameByClassNameFileSystemRector extends \Rector\Core\Rector\AbstractRector
{
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Rename file to respect class name', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
// app/SomeClass.php
class AnotherClass
{
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
// app/AnotherClass.php
class AnotherClass
{
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\ClassLike::class];
    }
    /**
     * @param ClassLike $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $className = $this->getName($node);
        if ($className === null) {
            return null;
        }
        $classShortName = $this->nodeNameResolver->getShortName($className);
        $smartFileInfo = $this->file->getSmartFileInfo();
        // matches
        if ($classShortName === $smartFileInfo->getBasenameWithoutSuffix()) {
            return null;
        }
        // no match → rename file
        $newFileLocation = $smartFileInfo->getPath() . \DIRECTORY_SEPARATOR . $classShortName . '.php';
        $this->removedAndAddedFilesCollector->addMovedFile($this->file, $newFileLocation);
        return null;
    }
}
