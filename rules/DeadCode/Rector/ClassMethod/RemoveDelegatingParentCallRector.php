<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\DeadCode\Rector\ClassMethod;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr;
use RectorPrefix20220606\PhpParser\Node\Expr\StaticCall;
use RectorPrefix20220606\PhpParser\Node\Stmt;
use RectorPrefix20220606\PhpParser\Node\Stmt\Class_;
use RectorPrefix20220606\PhpParser\Node\Stmt\ClassLike;
use RectorPrefix20220606\PhpParser\Node\Stmt\ClassMethod;
use RectorPrefix20220606\PhpParser\Node\Stmt\Expression;
use RectorPrefix20220606\PhpParser\Node\Stmt\Return_;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Rector\DeadCode\Comparator\CurrentAndParentClassMethodComparator;
use RectorPrefix20220606\Rector\Php80\NodeAnalyzer\PhpAttributeAnalyzer;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\ClassMethod\RemoveDelegatingParentCallRector\RemoveDelegatingParentCallRectorTest
 */
final class RemoveDelegatingParentCallRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private const ALLOWED_ANNOTATIONS = ['Route', 'required'];
    /**
     * @var string[]
     */
    private const ALLOWED_ATTRIBUTES = ['Symfony\\Component\\Routing\\Annotation\\Route', 'Symfony\\Contracts\\Service\\Attribute\\Required'];
    /**
     * @readonly
     * @var \Rector\DeadCode\Comparator\CurrentAndParentClassMethodComparator
     */
    private $currentAndParentClassMethodComparator;
    /**
     * @readonly
     * @var \Rector\Php80\NodeAnalyzer\PhpAttributeAnalyzer
     */
    private $phpAttributeAnalyzer;
    public function __construct(CurrentAndParentClassMethodComparator $currentAndParentClassMethodComparator, PhpAttributeAnalyzer $phpAttributeAnalyzer)
    {
        $this->currentAndParentClassMethodComparator = $currentAndParentClassMethodComparator;
        $this->phpAttributeAnalyzer = $phpAttributeAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Removed dead parent call, that does not change anything', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node) : ?Node
    {
        $classLike = $this->betterNodeFinder->findParentType($node, Class_::class);
        if ($this->shouldSkipClass($classLike)) {
            return null;
        }
        $onlyStmt = $this->matchClassMethodOnlyStmt($node);
        if ($onlyStmt === null) {
            return null;
        }
        // are both return?
        if ($this->isMethodReturnType($node, 'void') && !$onlyStmt instanceof Return_) {
            return null;
        }
        $staticCall = $this->matchStaticCall($onlyStmt);
        if (!$staticCall instanceof StaticCall) {
            return null;
        }
        if (!$this->currentAndParentClassMethodComparator->isParentCallMatching($node, $staticCall)) {
            return null;
        }
        if ($this->shouldSkipWithAnnotationsOrAttributes($node)) {
            return null;
        }
        // the method is just delegation, nothing extra
        $this->removeNode($node);
        return null;
    }
    private function shouldSkipClass(?ClassLike $classLike) : bool
    {
        if (!$classLike instanceof Class_) {
            return \true;
        }
        return $classLike->extends === null;
    }
    private function isMethodReturnType(ClassMethod $classMethod, string $type) : bool
    {
        if ($classMethod->returnType === null) {
            return \false;
        }
        return $this->isName($classMethod->returnType, $type);
    }
    /**
     * @param \PhpParser\Node\Expr|\PhpParser\Node\Stmt $node
     */
    private function matchStaticCall($node) : ?StaticCall
    {
        // must be static call
        if ($node instanceof Return_) {
            if ($node->expr instanceof StaticCall) {
                return $node->expr;
            }
            return null;
        }
        if ($node instanceof StaticCall) {
            return $node;
        }
        return null;
    }
    private function shouldSkipWithAnnotationsOrAttributes(ClassMethod $classMethod) : bool
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);
        if ($phpDocInfo->hasByNames(self::ALLOWED_ANNOTATIONS)) {
            return \true;
        }
        return $this->phpAttributeAnalyzer->hasPhpAttributes($classMethod, self::ALLOWED_ATTRIBUTES);
    }
    /**
     * @return null|\PhpParser\Node\Stmt|\PhpParser\Node\Expr
     */
    private function matchClassMethodOnlyStmt(ClassMethod $classMethod)
    {
        $classMethodStmts = $classMethod->stmts;
        if ($classMethodStmts === null) {
            return null;
        }
        if (\count($classMethodStmts) !== 1) {
            return null;
        }
        // recount empty notes
        $stmtsValues = \array_values($classMethodStmts);
        $stmtValue = $stmtsValues[0];
        if ($stmtValue instanceof Expression) {
            return $stmtValue->expr;
        }
        return $stmtValue;
    }
}
