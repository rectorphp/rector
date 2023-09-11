<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Rector\Core\Rector\AbstractRector;
use Rector\DeadCode\PhpDoc\TagRemover\ReturnTagRemover;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUselessReturnTagRector\RemoveUselessReturnTagRectorTest
 */
final class RemoveUselessReturnTagRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\DeadCode\PhpDoc\TagRemover\ReturnTagRemover
     */
    private $returnTagRemover;
    /**
     * @readonly
     * @var \Rector\Comments\NodeDocBlock\DocBlockUpdater
     */
    private $docBlockUpdater;
    public function __construct(ReturnTagRemover $returnTagRemover, DocBlockUpdater $docBlockUpdater)
    {
        $this->returnTagRemover = $returnTagRemover;
        $this->docBlockUpdater = $docBlockUpdater;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove @return docblock with same type as defined in PHP', [new CodeSample(<<<'CODE_SAMPLE'
use stdClass;

class SomeClass
{
    /**
     * @return stdClass
     */
    public function foo(): stdClass
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use stdClass;

class SomeClass
{
    public function foo(): stdClass
    {
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node) : ?Node
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        $hasChanged = $this->returnTagRemover->removeReturnTagIfUseless($phpDocInfo, $node);
        if (!$hasChanged) {
            return null;
        }
        $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($node);
        return $node;
    }
}
