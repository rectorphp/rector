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
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Php\PhpVersionProvider;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersion;
use ReflectionClass;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix20220531\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\DeadCode\Rector\ConstFetch\RemovePhpVersionIdCheckRector\RemovePhpVersionIdCheckRectorTest
 */
final class RemovePhpVersionIdCheckRector extends \Rector\Core\Rector\AbstractRector implements \Rector\Core\Contract\Rector\ConfigurableRectorInterface
{
    /**
     * @var PhpVersion::*|null
     */
    private $phpVersion = null;
    /**
     * @readonly
     * @var \Rector\Core\Php\PhpVersionProvider
     */
    private $phpVersionProvider;
    public function __construct(\Rector\Core\Php\PhpVersionProvider $phpVersionProvider)
    {
        $this->phpVersionProvider = $phpVersionProvider;
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        $phpVersion = $configuration[0];
        \RectorPrefix20220531\Webmozart\Assert\Assert::integer($phpVersion);
        // get all constants
        $phpVersionReflectionClass = new \ReflectionClass(\Rector\Core\ValueObject\PhpVersion::class);
        // @todo check
        if (\in_array($phpVersion, $phpVersionReflectionClass->getConstants(), \true)) {
            return;
        }
        // ensure cast to (string) first to allow string like "8.0" value to be converted to the int value
        /** @var PhpVersion::* $phpVersion */
        $this->phpVersion = $phpVersion;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Remove unneeded PHP_VERSION_ID conditional checks', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(<<<'CODE_SAMPLE'
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
        return [\PhpParser\Node\Stmt\If_::class];
    }
    /**
     * @param If_ $node
     * @return null|\PhpParser\Node\Stmt\If_|mixed[]
     */
    public function refactor(\PhpParser\Node $node)
    {
        /**
         * $this->phpVersionProvider->provide() fallback is here as $currentFileProvider must be accessed after initialization
         */
        if ($this->phpVersion === null) {
            $this->phpVersion = $this->phpVersionProvider->provide();
        }
        if (!$node->cond instanceof \PhpParser\Node\Expr\BinaryOp) {
            return null;
        }
        $binaryOp = $node->cond;
        if ($binaryOp->left instanceof \PhpParser\Node\Expr\ConstFetch && $this->isName($binaryOp->left->name, 'PHP_VERSION_ID')) {
            return $this->refactorConstFetch($binaryOp->left, $node, $binaryOp);
        }
        if (!$binaryOp->right instanceof \PhpParser\Node\Expr\ConstFetch) {
            return null;
        }
        if (!$this->isName($binaryOp->right->name, 'PHP_VERSION_ID')) {
            return null;
        }
        return $this->refactorConstFetch($binaryOp->right, $node, $binaryOp);
    }
    /**
     * @return null|\PhpParser\Node\Stmt\If_|mixed[]
     */
    private function processSmaller(\PhpParser\Node\Expr\ConstFetch $constFetch, \PhpParser\Node\Expr\BinaryOp\Smaller $smaller, \PhpParser\Node\Stmt\If_ $if)
    {
        if ($smaller->left === $constFetch) {
            return $this->processSmallerLeft($smaller, $if);
        }
        if ($smaller->right === $constFetch) {
            return $this->processSmallerRight($smaller, $if);
        }
        return null;
    }
    /**
     * @return null|\PhpParser\Node\Stmt\If_|mixed[]
     */
    private function processGreaterOrEqual(\PhpParser\Node\Expr\ConstFetch $constFetch, \PhpParser\Node\Expr\BinaryOp\GreaterOrEqual $greaterOrEqual, \PhpParser\Node\Stmt\If_ $if)
    {
        if ($greaterOrEqual->left === $constFetch) {
            return $this->processGreaterOrEqualLeft($greaterOrEqual, $if);
        }
        if ($greaterOrEqual->right === $constFetch) {
            return $this->processGreaterOrEqualRight($greaterOrEqual, $if);
        }
        return null;
    }
    private function processSmallerLeft(\PhpParser\Node\Expr\BinaryOp\Smaller $smaller, \PhpParser\Node\Stmt\If_ $if) : ?\PhpParser\Node\Stmt\If_
    {
        $value = $smaller->right;
        if (!$value instanceof \PhpParser\Node\Scalar\LNumber) {
            return null;
        }
        if ($this->phpVersion >= $value->value) {
            $this->removeNode($if);
            return $if;
        }
        return null;
    }
    /**
     * @return null|\PhpParser\Node\Stmt\If_|mixed[]
     */
    private function processSmallerRight(\PhpParser\Node\Expr\BinaryOp\Smaller $smaller, \PhpParser\Node\Stmt\If_ $if)
    {
        $value = $smaller->left;
        if (!$value instanceof \PhpParser\Node\Scalar\LNumber) {
            return null;
        }
        if ($this->phpVersion < $value->value) {
            return null;
        }
        if ($if->stmts === []) {
            $this->removeNode($if);
            return $if;
        }
        return $if->stmts;
    }
    /**
     * @return null|\PhpParser\Node\Stmt\If_|mixed[]
     */
    private function processGreaterOrEqualLeft(\PhpParser\Node\Expr\BinaryOp\GreaterOrEqual $greaterOrEqual, \PhpParser\Node\Stmt\If_ $if)
    {
        $value = $greaterOrEqual->right;
        if (!$value instanceof \PhpParser\Node\Scalar\LNumber) {
            return null;
        }
        if ($this->phpVersion < $value->value) {
            return null;
        }
        if ($if->stmts === []) {
            $this->removeNode($if);
            return $if;
        }
        return $if->stmts;
    }
    private function processGreaterOrEqualRight(\PhpParser\Node\Expr\BinaryOp\GreaterOrEqual $greaterOrEqual, \PhpParser\Node\Stmt\If_ $if) : ?\PhpParser\Node\Stmt\If_
    {
        $value = $greaterOrEqual->left;
        if (!$value instanceof \PhpParser\Node\Scalar\LNumber) {
            return null;
        }
        if ($this->phpVersion >= $value->value) {
            $this->removeNode($if);
            return $if;
        }
        return null;
    }
    /**
     * @return null|\PhpParser\Node\Stmt\If_|mixed[]
     */
    private function processGreater(\PhpParser\Node\Expr\ConstFetch $constFetch, \PhpParser\Node\Expr\BinaryOp\Greater $greater, \PhpParser\Node\Stmt\If_ $if)
    {
        if ($greater->left === $constFetch) {
            return $this->processGreaterLeft($greater, $if);
        }
        if ($greater->right === $constFetch) {
            return $this->processGreaterRight($greater, $if);
        }
        return null;
    }
    /**
     * @return null|\PhpParser\Node\Stmt\If_|mixed[]
     */
    private function processGreaterLeft(\PhpParser\Node\Expr\BinaryOp\Greater $greater, \PhpParser\Node\Stmt\If_ $if)
    {
        $value = $greater->right;
        if (!$value instanceof \PhpParser\Node\Scalar\LNumber) {
            return null;
        }
        if ($this->phpVersion < $value->value) {
            return null;
        }
        if ($if->stmts === []) {
            $this->removeNode($if);
            return $if;
        }
        return $if->stmts;
    }
    private function processGreaterRight(\PhpParser\Node\Expr\BinaryOp\Greater $greater, \PhpParser\Node\Stmt\If_ $if) : ?\PhpParser\Node\Stmt\If_
    {
        $value = $greater->left;
        if (!$value instanceof \PhpParser\Node\Scalar\LNumber) {
            return null;
        }
        if ($this->phpVersion >= $value->value) {
            $this->removeNode($if);
            return $if;
        }
        return null;
    }
    /**
     * @return null|\PhpParser\Node\Stmt\If_|mixed[]
     */
    private function refactorConstFetch(\PhpParser\Node\Expr\ConstFetch $constFetch, \PhpParser\Node\Stmt\If_ $if, \PhpParser\Node\Expr\BinaryOp $binaryOp)
    {
        if ($binaryOp instanceof \PhpParser\Node\Expr\BinaryOp\Smaller) {
            return $this->processSmaller($constFetch, $binaryOp, $if);
        }
        if ($binaryOp instanceof \PhpParser\Node\Expr\BinaryOp\GreaterOrEqual) {
            return $this->processGreaterOrEqual($constFetch, $binaryOp, $if);
        }
        if ($binaryOp instanceof \PhpParser\Node\Expr\BinaryOp\Greater) {
            return $this->processGreater($constFetch, $binaryOp, $if);
        }
        return null;
    }
}
