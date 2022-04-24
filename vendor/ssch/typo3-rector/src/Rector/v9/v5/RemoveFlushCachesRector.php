<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v9\v5;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/9.5/Deprecation-85981-AnnotationFlushesCaches.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v9\v5\RemoveFlushCachesRector\RemoveFlushCachesRectorTest
 */
final class RemoveFlushCachesRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover
     */
    private $phpDocTagRemover;
    public function __construct(\Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover $phpDocTagRemover)
    {
        $this->phpDocTagRemover = $phpDocTagRemover;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        /** @var string $name */
        $name = $this->getName($node);
        if (\substr_compare($name, 'Command', -\strlen('Command')) !== 0) {
            return null;
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        $this->phpDocTagRemover->removeByName($phpDocInfo, 'flushCaches');
        return $node;
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Remove @flushesCaches annotation', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
/**
 * My command
 *
 * @flushesCaches
 */
public function myCommand()
{
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
/**
 * My Command
 */
public function myCommand()
{
}

CODE_SAMPLE
)]);
    }
}
