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
use PhpParser\Node\Stmt\If_;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Php\PhpVersionProvider;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\Util\PhpVersionFactory;
use Rector\Core\ValueObject\PhpVersion;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\ConstFetch\RemovePhpVersionIdCheckRector\RemovePhpVersionIdCheckRectorTest
 */
final class RemovePhpVersionIdCheckRector extends \Rector\Core\Rector\AbstractRector implements \Rector\Core\Contract\Rector\ConfigurableRectorInterface
{
    /**
     * @var string|int|null
     */
    private $phpVersionConstraint = null;
    /**
     * @readonly
     * @var \Rector\Core\Util\PhpVersionFactory
     */
    private $phpVersionFactory;
    /**
     * @readonly
     * @var \Rector\Core\Php\PhpVersionProvider
     */
    private $phpVersionProvider;
    public function __construct(\Rector\Core\Util\PhpVersionFactory $phpVersionFactory, \Rector\Core\Php\PhpVersionProvider $phpVersionProvider)
    {
        $this->phpVersionFactory = $phpVersionFactory;
        $this->phpVersionProvider = $phpVersionProvider;
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        $this->phpVersionConstraint = \array_pop($configuration);
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Remove unneeded PHP_VERSION_ID check', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(<<<'CODE_SAMPLE'
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
, [\Rector\Core\ValueObject\PhpVersion::PHP_80])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\ConstFetch::class];
    }
    /**
     * @param ConstFetch $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->isName($node, 'PHP_VERSION_ID')) {
            return null;
        }
        /**
         * $this->phpVersionProvider->provide() fallback is here as $currentFileProvider must be accessed after initialization
         */
        $phpVersionConstraint = $this->phpVersionConstraint ?? $this->phpVersionProvider->provide();
        // ensure cast to (string) first to allow string like "8.0" value to be converted to the int value
        $this->phpVersionConstraint = $this->phpVersionFactory->createIntVersion((string) $phpVersionConstraint);
        $if = $this->betterNodeFinder->findParentType($node, \PhpParser\Node\Stmt\If_::class);
        if (!$if instanceof \PhpParser\Node\Stmt\If_) {
            return null;
        }
        $parent = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if ($this->shouldSkip($node, $if, $parent)) {
            return null;
        }
        if ($parent instanceof \PhpParser\Node\Expr\BinaryOp\Smaller) {
            return $this->processSmaller($node, $parent, $if);
        }
        if ($parent instanceof \PhpParser\Node\Expr\BinaryOp\GreaterOrEqual) {
            return $this->processGreaterOrEqual($node, $parent, $if);
        }
        if ($parent instanceof \PhpParser\Node\Expr\BinaryOp\Greater) {
            return $this->processGreater($node, $parent, $if);
        }
        return null;
    }
    private function shouldSkip(\PhpParser\Node\Expr\ConstFetch $constFetch, \PhpParser\Node\Stmt\If_ $if, ?\PhpParser\Node $node) : bool
    {
        $if = $this->betterNodeFinder->findParentType($constFetch, \PhpParser\Node\Stmt\If_::class);
        if (!$if instanceof \PhpParser\Node\Stmt\If_) {
            return \true;
        }
        $node = $constFetch->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if (!$node instanceof \PhpParser\Node\Expr\BinaryOp) {
            return \true;
        }
        return $if->cond !== $node;
    }
    private function processSmaller(\PhpParser\Node\Expr\ConstFetch $constFetch, \PhpParser\Node\Expr\BinaryOp\Smaller $smaller, \PhpParser\Node\Stmt\If_ $if) : ?\PhpParser\Node\Expr\ConstFetch
    {
        if ($smaller->left === $constFetch) {
            return $this->processSmallerLeft($constFetch, $smaller, $if);
        }
        if ($smaller->right === $constFetch) {
            return $this->processSmallerRight($constFetch, $smaller, $if);
        }
        return null;
    }
    private function processGreaterOrEqual(\PhpParser\Node\Expr\ConstFetch $constFetch, \PhpParser\Node\Expr\BinaryOp\GreaterOrEqual $greaterOrEqual, \PhpParser\Node\Stmt\If_ $if) : ?\PhpParser\Node\Expr\ConstFetch
    {
        if ($greaterOrEqual->left === $constFetch) {
            return $this->processGreaterOrEqualLeft($constFetch, $greaterOrEqual, $if);
        }
        if ($greaterOrEqual->right === $constFetch) {
            return $this->processGreaterOrEqualRight($constFetch, $greaterOrEqual, $if);
        }
        return null;
    }
    private function processSmallerLeft(\PhpParser\Node\Expr\ConstFetch $constFetch, \PhpParser\Node\Expr\BinaryOp\Smaller $smaller, \PhpParser\Node\Stmt\If_ $if) : ?\PhpParser\Node\Expr\ConstFetch
    {
        $value = $smaller->right;
        if (!$value instanceof \PhpParser\Node\Scalar\LNumber) {
            return null;
        }
        if ($this->phpVersionConstraint >= $value->value) {
            $this->removeNode($if);
        }
        return $constFetch;
    }
    private function processSmallerRight(\PhpParser\Node\Expr\ConstFetch $constFetch, \PhpParser\Node\Expr\BinaryOp\Smaller $smaller, \PhpParser\Node\Stmt\If_ $if) : ?\PhpParser\Node\Expr\ConstFetch
    {
        $value = $smaller->left;
        if (!$value instanceof \PhpParser\Node\Scalar\LNumber) {
            return null;
        }
        if ($this->phpVersionConstraint >= $value->value) {
            $this->nodesToAddCollector->addNodesBeforeNode($if->stmts, $if);
            $this->removeNode($if);
        }
        return $constFetch;
    }
    private function processGreaterOrEqualLeft(\PhpParser\Node\Expr\ConstFetch $constFetch, \PhpParser\Node\Expr\BinaryOp\GreaterOrEqual $greaterOrEqual, \PhpParser\Node\Stmt\If_ $if) : ?\PhpParser\Node\Expr\ConstFetch
    {
        $value = $greaterOrEqual->right;
        if (!$value instanceof \PhpParser\Node\Scalar\LNumber) {
            return null;
        }
        if ($this->phpVersionConstraint >= $value->value) {
            $this->nodesToAddCollector->addNodesBeforeNode($if->stmts, $if);
            $this->removeNode($if);
        }
        return $constFetch;
    }
    private function processGreaterOrEqualRight(\PhpParser\Node\Expr\ConstFetch $constFetch, \PhpParser\Node\Expr\BinaryOp\GreaterOrEqual $greaterOrEqual, \PhpParser\Node\Stmt\If_ $if) : ?\PhpParser\Node\Expr\ConstFetch
    {
        $value = $greaterOrEqual->left;
        if (!$value instanceof \PhpParser\Node\Scalar\LNumber) {
            return null;
        }
        if ($this->phpVersionConstraint >= $value->value) {
            $this->removeNode($if);
        }
        return $constFetch;
    }
    private function processGreater(\PhpParser\Node\Expr\ConstFetch $constFetch, \PhpParser\Node\Expr\BinaryOp\Greater $greater, \PhpParser\Node\Stmt\If_ $if) : ?\PhpParser\Node\Expr\ConstFetch
    {
        if ($greater->left === $constFetch) {
            return $this->processGreaterLeft($constFetch, $greater, $if);
        }
        if ($greater->right === $constFetch) {
            return $this->processGreaterRight($constFetch, $greater, $if);
        }
        return null;
    }
    private function processGreaterLeft(\PhpParser\Node\Expr\ConstFetch $constFetch, \PhpParser\Node\Expr\BinaryOp\Greater $greater, \PhpParser\Node\Stmt\If_ $if) : ?\PhpParser\Node\Expr\ConstFetch
    {
        $value = $greater->right;
        if (!$value instanceof \PhpParser\Node\Scalar\LNumber) {
            return null;
        }
        if ($this->phpVersionConstraint >= $value->value) {
            $this->nodesToAddCollector->addNodesBeforeNode($if->stmts, $if);
            $this->removeNode($if);
        }
        return $constFetch;
    }
    private function processGreaterRight(\PhpParser\Node\Expr\ConstFetch $constFetch, \PhpParser\Node\Expr\BinaryOp\Greater $greater, \PhpParser\Node\Stmt\If_ $if) : ?\PhpParser\Node\Expr\ConstFetch
    {
        $value = $greater->left;
        if (!$value instanceof \PhpParser\Node\Scalar\LNumber) {
            return null;
        }
        if ($this->phpVersionConstraint >= $value->value) {
            $this->removeNode($if);
        }
        return $constFetch;
    }
}
