<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\ConstFetch;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\Greater;
use PhpParser\Node\Expr\BinaryOp\GreaterOrEqual;
use PhpParser\Node\Expr\BinaryOp\Smaller;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\If_;
use PhpParser\NodeTraverser;
use Rector\Core\Php\PhpVersionProvider;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersion;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\ConstFetch\RemovePhpVersionIdCheckRector\RemovePhpVersionIdCheckRectorTest
 */
final class RemovePhpVersionIdCheckRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Core\Php\PhpVersionProvider
     */
    private $phpVersionProvider;
    /**
     * @var PhpVersion::*|null
     */
    private $phpVersion = null;
    public function __construct(PhpVersionProvider $phpVersionProvider)
    {
        $this->phpVersionProvider = $phpVersionProvider;
        $this->phpVersion = $this->phpVersionProvider->provide();
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove unneeded PHP_VERSION_ID conditional checks', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        if (PHP_VERSION_ID < 80000) {
            return;
        }

        echo 'do something';
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        echo 'do something';
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
        return [If_::class];
    }
    /**
     * @param If_ $node
     * @return null|int|Stmt[]
     */
    public function refactor(Node $node)
    {
        /**
         * $this->phpVersionProvider->provide() fallback is here as $currentFileProvider must be accessed after initialization
         */
        if ($this->phpVersion === null) {
            $this->phpVersion = $this->phpVersionProvider->provide();
        }
        if (!$node->cond instanceof BinaryOp) {
            return null;
        }
        $binaryOp = $node->cond;
        if ($binaryOp->left instanceof ConstFetch && $this->isName($binaryOp->left->name, 'PHP_VERSION_ID')) {
            return $this->refactorConstFetch($binaryOp->left, $node, $binaryOp);
        }
        if (!$binaryOp->right instanceof ConstFetch) {
            return null;
        }
        if (!$this->isName($binaryOp->right->name, 'PHP_VERSION_ID')) {
            return null;
        }
        return $this->refactorConstFetch($binaryOp->right, $node, $binaryOp);
    }
    /**
     * @return null|Stmt[]|int
     */
    private function refactorSmaller(ConstFetch $constFetch, Smaller $smaller, If_ $if)
    {
        if ($smaller->left === $constFetch) {
            return $this->refactorSmallerLeft($smaller);
        }
        if ($smaller->right === $constFetch) {
            return $this->refactorSmallerRight($smaller, $if);
        }
        return null;
    }
    /**
     * @return null|int|Stmt[]
     */
    private function processGreaterOrEqual(ConstFetch $constFetch, GreaterOrEqual $greaterOrEqual, If_ $if)
    {
        if ($greaterOrEqual->left === $constFetch) {
            return $this->refactorGreaterOrEqualLeft($greaterOrEqual, $if);
        }
        if ($greaterOrEqual->right === $constFetch) {
            return $this->refactorGreaterOrEqualRight($greaterOrEqual);
        }
        return null;
    }
    private function refactorSmallerLeft(Smaller $smaller) : ?int
    {
        $value = $smaller->right;
        if (!$value instanceof LNumber) {
            return null;
        }
        if ($this->phpVersion >= $value->value) {
            return NodeTraverser::REMOVE_NODE;
        }
        return null;
    }
    /**
     * @return null|Stmt[]|int
     */
    private function refactorSmallerRight(Smaller $smaller, If_ $if)
    {
        $value = $smaller->left;
        if (!$value instanceof LNumber) {
            return null;
        }
        if ($this->phpVersion < $value->value) {
            return null;
        }
        if ($if->stmts === []) {
            return NodeTraverser::REMOVE_NODE;
        }
        return $if->stmts;
    }
    /**
     * @return null|Stmt[]|int
     */
    private function refactorGreaterOrEqualLeft(GreaterOrEqual $greaterOrEqual, If_ $if)
    {
        $value = $greaterOrEqual->right;
        if (!$value instanceof LNumber) {
            return null;
        }
        if ($this->phpVersion < $value->value) {
            return null;
        }
        if ($if->stmts === []) {
            return NodeTraverser::REMOVE_NODE;
        }
        return $if->stmts;
    }
    private function refactorGreaterOrEqualRight(GreaterOrEqual $greaterOrEqual) : ?int
    {
        $value = $greaterOrEqual->left;
        if (!$value instanceof LNumber) {
            return null;
        }
        if ($this->phpVersion >= $value->value) {
            return NodeTraverser::REMOVE_NODE;
        }
        return null;
    }
    /**
     * @return null|Stmt[]|int
     */
    private function refactorGreater(ConstFetch $constFetch, Greater $greater, If_ $if)
    {
        if ($greater->left === $constFetch) {
            return $this->refactorGreaterLeft($greater, $if);
        }
        if ($greater->right === $constFetch) {
            return $this->refactorGreaterRight($greater);
        }
        return null;
    }
    /**
     * @return null|Stmt[]|int
     */
    private function refactorGreaterLeft(Greater $greater, If_ $if)
    {
        $value = $greater->right;
        if (!$value instanceof LNumber) {
            return null;
        }
        if ($this->phpVersion < $value->value) {
            return null;
        }
        if ($if->stmts === []) {
            return NodeTraverser::REMOVE_NODE;
        }
        return $if->stmts;
    }
    private function refactorGreaterRight(Greater $greater) : ?int
    {
        $value = $greater->left;
        if (!$value instanceof LNumber) {
            return null;
        }
        if ($this->phpVersion >= $value->value) {
            return NodeTraverser::REMOVE_NODE;
        }
        return null;
    }
    /**
     * @return null|Stmt[]|int
     */
    private function refactorConstFetch(ConstFetch $constFetch, If_ $if, BinaryOp $binaryOp)
    {
        if ($binaryOp instanceof Smaller) {
            return $this->refactorSmaller($constFetch, $binaryOp, $if);
        }
        if ($binaryOp instanceof GreaterOrEqual) {
            return $this->processGreaterOrEqual($constFetch, $binaryOp, $if);
        }
        if ($binaryOp instanceof Greater) {
            return $this->refactorGreater($constFetch, $binaryOp, $if);
        }
        return null;
    }
}
