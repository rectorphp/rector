<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\StmtsAwareInterface;

use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Nop;
use Rector\PhpParser\Enum\NodeGroup;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\StmtsAwareInterface\RemoveDeadInstanceOfAssertRector\RemoveDeadInstanceOfAssertRectorTest
 */
final class RemoveDeadInstanceOfAssertRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove assert() with instanceof check on a value whose type is already known', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function __construct(
        private UserRepository $userRepository
    ) {
    }

    public function run(): void
    {
        assert($this->userRepository instanceof UserRepository);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function __construct(
        private UserRepository $userRepository
    ) {
    }

    public function run(): void
    {
    }
}
CODE_SAMPLE
)]);
    }
    public function getNodeTypes(): array
    {
        return NodeGroup::STMTS_AWARE;
    }
    /**
     * @param StmtsAware $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->stmts === null) {
            return null;
        }
        $hasChanged = \false;
        foreach ($node->stmts as $key => $stmt) {
            if (!$stmt instanceof Expression) {
                continue;
            }
            if (!$this->isDeadInstanceOfAssert($stmt->expr)) {
                continue;
            }
            $docComment = $stmt->getDocComment();
            if ($docComment instanceof Doc) {
                $nop = new Nop();
                $nop->setDocComment($docComment);
                $node->stmts[$key] = $nop;
            } else {
                unset($node->stmts[$key]);
            }
            $hasChanged = \true;
        }
        if (!$hasChanged) {
            return null;
        }
        $node->stmts = array_values($node->stmts);
        return $node;
    }
    private function isDeadInstanceOfAssert(Expr $expr): bool
    {
        if (!$expr instanceof FuncCall) {
            return \false;
        }
        if ($expr->isFirstClassCallable()) {
            return \false;
        }
        if (!$this->isName($expr, 'assert')) {
            return \false;
        }
        // single-arg assert only; keep asserts that carry a description message
        if (count($expr->getArgs()) !== 1) {
            return \false;
        }
        $instanceof = $expr->getArgs()[0]->value;
        if (!$instanceof instanceof Instanceof_) {
            return \false;
        }
        // only property fetch or variable value
        if (!$instanceof->expr instanceof PropertyFetch && !$instanceof->expr instanceof Variable) {
            return \false;
        }
        if (!$instanceof->class instanceof Name) {
            return \false;
        }
        $classType = $this->nodeTypeResolver->getType($instanceof->class);
        $exprType = $this->nodeTypeResolver->getType($instanceof->expr);
        if ($classType->equals($exprType)) {
            return \true;
        }
        return $classType->isSuperTypeOf($exprType)->yes();
    }
}
