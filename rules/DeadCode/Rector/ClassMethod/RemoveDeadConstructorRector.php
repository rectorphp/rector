<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\NodeAnalyzer\ParamAnalyzer;
use Rector\Core\NodeManipulator\ClassMethodManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\MethodName;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\ClassMethod\RemoveDeadConstructorRector\RemoveDeadConstructorRectorTest
 */
final class RemoveDeadConstructorRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\ClassMethodManipulator
     */
    private $classMethodManipulator;
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\ParamAnalyzer
     */
    private $paramAnalyzer;
    public function __construct(\Rector\Core\NodeManipulator\ClassMethodManipulator $classMethodManipulator, \Rector\Core\NodeAnalyzer\ParamAnalyzer $paramAnalyzer)
    {
        $this->classMethodManipulator = $classMethodManipulator;
        $this->paramAnalyzer = $paramAnalyzer;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Remove empty constructor', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function __construct()
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
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
        return [\PhpParser\Node\Stmt\ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $classLike = $this->betterNodeFinder->findParentType($node, \PhpParser\Node\Stmt\Class_::class);
        if (!$classLike instanceof \PhpParser\Node\Stmt\Class_) {
            return null;
        }
        if ($this->shouldSkipPropertyPromotion($node)) {
            return null;
        }
        if ($classLike->extends instanceof \PhpParser\Node\Name\FullyQualified) {
            return null;
        }
        $this->removeNode($node);
        return null;
    }
    private function shouldSkipPropertyPromotion(\PhpParser\Node\Stmt\ClassMethod $classMethod) : bool
    {
        if (!$this->isName($classMethod, \Rector\Core\ValueObject\MethodName::CONSTRUCT)) {
            return \true;
        }
        if ($classMethod->stmts === null) {
            return \true;
        }
        if ($classMethod->stmts !== []) {
            return \true;
        }
        if ($this->paramAnalyzer->hasPropertyPromotion($classMethod->params)) {
            return \true;
        }
        return $this->classMethodManipulator->isNamedConstructor($classMethod);
    }
}
