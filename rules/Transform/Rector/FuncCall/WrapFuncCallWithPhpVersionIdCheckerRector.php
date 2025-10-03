<?php

declare (strict_types=1);
namespace Rector\Transform\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\BinaryOp\Smaller;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\Int_;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use PhpParser\NodeVisitor;
use Rector\Contract\PhpParser\Node\StmtsAwareInterface;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Rector\AbstractRector;
use Rector\Transform\ValueObject\WrapFuncCallWithPhpVersionIdChecker;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix202510\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Transform\Rector\FuncCall\WrapFuncCallWithPhpVersionIdCheckerRector\WrapFuncCallWithPhpVersionIdCheckerRectorTest
 */
final class WrapFuncCallWithPhpVersionIdCheckerRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var WrapFuncCallWithPhpVersionIdChecker[]
     */
    private array $wrapFuncCallWithPhpVersionIdCheckers = [];
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Wraps function calls without assignment in a condition to check for a PHP version id', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
no_op_function();
CODE_SAMPLE
, <<<'CODE_SAMPLE'
if (function_exists('no_op_function') && PHP_VERSION_ID < 80500) {
    no_op_function();
}
CODE_SAMPLE
, [new WrapFuncCallWithPhpVersionIdChecker('no_op_function', 80500)])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [StmtsAwareInterface::class];
    }
    /**
     * @param StmtsAwareInterface $node
     * @return null|Node|NodeVisitor::DONT_TRAVERSE_CHILDREN
     */
    public function refactor(Node $node)
    {
        if ($node->stmts === null) {
            return null;
        }
        if ($this->isWrappedFuncCall($node)) {
            return NodeVisitor::DONT_TRAVERSE_CHILDREN;
        }
        $hasChanged = \false;
        foreach ($node->stmts as $key => $stmt) {
            if (!$stmt instanceof Expression) {
                continue;
            }
            if (!$stmt->expr instanceof FuncCall) {
                continue;
            }
            $funcCall = $stmt->expr;
            foreach ($this->wrapFuncCallWithPhpVersionIdCheckers as $wrapFuncCallWithPhpVersionIdChecker) {
                if ($this->getName($funcCall) !== $wrapFuncCallWithPhpVersionIdChecker->getFunctionName()) {
                    continue;
                }
                $phpVersionIdConst = new ConstFetch(new Name('PHP_VERSION_ID'));
                $if = new If_(new BooleanAnd(new FuncCall(new Name('function_exists'), [new Arg(new String_($wrapFuncCallWithPhpVersionIdChecker->getFunctionName()))]), new Smaller($phpVersionIdConst, new Int_($wrapFuncCallWithPhpVersionIdChecker->getPhpVersionId()))));
                $if->stmts = [$stmt];
                $node->stmts[$key] = $if;
                $hasChanged = \true;
            }
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    public function configure(array $configuration): void
    {
        Assert::allIsInstanceOf($configuration, WrapFuncCallWithPhpVersionIdChecker::class);
        $this->wrapFuncCallWithPhpVersionIdCheckers = $configuration;
    }
    private function isWrappedFuncCall(StmtsAwareInterface $stmtsAware): bool
    {
        if (!$stmtsAware instanceof If_) {
            return \false;
        }
        $phpVersionId = $this->getPhpVersionId($stmtsAware->cond);
        if (!$phpVersionId instanceof Int_) {
            return \false;
        }
        if (count($stmtsAware->stmts) !== 1) {
            return \false;
        }
        $childStmt = $stmtsAware->stmts[0];
        if (!$childStmt instanceof Expression || !$childStmt->expr instanceof FuncCall) {
            return \false;
        }
        foreach ($this->wrapFuncCallWithPhpVersionIdCheckers as $wrapFuncCallWithPhpVersionIdChecker) {
            if ($this->getName($childStmt->expr) !== $wrapFuncCallWithPhpVersionIdChecker->getFunctionName()) {
                continue;
            }
            if ($phpVersionId->value !== $wrapFuncCallWithPhpVersionIdChecker->getPhpVersionId()) {
                continue;
            }
            return \true;
        }
        return \false;
    }
    private function getPhpVersionId(Expr $expr): ?Int_
    {
        if ($expr instanceof BooleanAnd) {
            return $this->getPhpVersionId($expr->left) ?? $this->getPhpVersionId($expr->right);
        }
        if (!$expr instanceof Smaller) {
            return null;
        }
        if (!$expr->left instanceof ConstFetch || !$this->isName($expr->left->name, 'PHP_VERSION_ID')) {
            return null;
        }
        if (!$expr->right instanceof Int_) {
            return null;
        }
        return $expr->right;
    }
}
