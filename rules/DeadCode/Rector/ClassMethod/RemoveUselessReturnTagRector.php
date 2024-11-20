<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Rector\DeadCode\PhpDoc\TagRemover\ReturnTagRemover;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUselessReturnTagRector\RemoveUselessReturnTagRectorTest
 */
final class RemoveUselessReturnTagRector extends AbstractRector
{
    /**
     * @readonly
     */
    private ReturnTagRemover $returnTagRemover;
    /**
     * @readonly
     */
    private DocBlockUpdater $docBlockUpdater;
    /**
     * @readonly
     */
    private PhpDocInfoFactory $phpDocInfoFactory;
    public function __construct(ReturnTagRemover $returnTagRemover, DocBlockUpdater $docBlockUpdater, PhpDocInfoFactory $phpDocInfoFactory)
    {
        $this->returnTagRemover = $returnTagRemover;
        $this->docBlockUpdater = $docBlockUpdater;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
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
        return [ClassMethod::class, Function_::class];
    }
    /**
     * @param ClassMethod|Function_ $node
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
