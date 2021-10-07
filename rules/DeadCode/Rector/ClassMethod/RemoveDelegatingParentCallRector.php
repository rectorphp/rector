<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\Rector\AbstractRector;
use Rector\DeadCode\Comparator\CurrentAndParentClassMethodComparator;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\ClassMethod\RemoveDelegatingParentCallRector\RemoveDelegatingParentCallRectorTest
 */
final class RemoveDelegatingParentCallRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var \Rector\DeadCode\Comparator\CurrentAndParentClassMethodComparator
     */
    private $currentAndParentClassMethodComparator;
    public function __construct(\Rector\DeadCode\Comparator\CurrentAndParentClassMethodComparator $currentAndParentClassMethodComparator)
    {
        $this->currentAndParentClassMethodComparator = $currentAndParentClassMethodComparator;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Removed dead parent call, that does not change anything', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function prettyPrint(array $stmts): string
    {
        return parent::prettyPrint($stmts);
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
        $classLike = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NODE);
        if ($this->shouldSkipClass($classLike)) {
            return null;
        }
        $onlyStmt = $this->matchClassMethodOnlyStmt($node);
        if ($onlyStmt === null) {
            return null;
        }
        // are both return?
        if ($this->isMethodReturnType($node, 'void') && !$onlyStmt instanceof \PhpParser\Node\Stmt\Return_) {
            return null;
        }
        $staticCall = $this->matchStaticCall($onlyStmt);
        if (!$staticCall instanceof \PhpParser\Node\Expr\StaticCall) {
            return null;
        }
        if (!$this->currentAndParentClassMethodComparator->isParentCallMatching($node, $staticCall)) {
            return null;
        }
        if ($this->hasRequiredAnnotation($node)) {
            return null;
        }
        // the method is just delegation, nothing extra
        $this->removeNode($node);
        return null;
    }
    private function shouldSkipClass(?\PhpParser\Node\Stmt\ClassLike $classLike) : bool
    {
        if (!$classLike instanceof \PhpParser\Node\Stmt\Class_) {
            return \true;
        }
        return $classLike->extends === null;
    }
    private function isMethodReturnType(\PhpParser\Node\Stmt\ClassMethod $classMethod, string $type) : bool
    {
        if ($classMethod->returnType === null) {
            return \false;
        }
        return $this->isName($classMethod->returnType, $type);
    }
    private function matchStaticCall(\PhpParser\Node $node) : ?\PhpParser\Node\Expr\StaticCall
    {
        // must be static call
        if ($node instanceof \PhpParser\Node\Stmt\Return_) {
            if ($node->expr instanceof \PhpParser\Node\Expr\StaticCall) {
                return $node->expr;
            }
            return null;
        }
        if ($node instanceof \PhpParser\Node\Expr\StaticCall) {
            return $node;
        }
        return null;
    }
    private function hasRequiredAnnotation(\PhpParser\Node\Stmt\ClassMethod $classMethod) : bool
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);
        return $phpDocInfo->hasByName('required');
    }
    /**
     * @return null|\PhpParser\Node\Stmt|\PhpParser\Node\Expr
     */
    private function matchClassMethodOnlyStmt(\PhpParser\Node\Stmt\ClassMethod $classMethod)
    {
        if ($classMethod->stmts === null) {
            return null;
        }
        if (\count((array) $classMethod->stmts) !== 1) {
            return null;
        }
        // recount empty notes
        $stmtsValues = \array_values($classMethod->stmts);
        return $this->unwrapExpression($stmtsValues[0]);
    }
}
