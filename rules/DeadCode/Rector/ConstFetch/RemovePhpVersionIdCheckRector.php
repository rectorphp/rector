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
use RectorPrefix202212\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\DeadCode\Rector\ConstFetch\RemovePhpVersionIdCheckRector\RemovePhpVersionIdCheckRectorTest
 */
final class RemovePhpVersionIdCheckRector extends AbstractRector implements ConfigurableRectorInterface
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
    public function __construct(PhpVersionProvider $phpVersionProvider)
    {
        $this->phpVersionProvider = $phpVersionProvider;
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        $phpVersion = $configuration[0];
        Assert::integer($phpVersion);
        // get all constants
        $phpVersionReflectionClass = new ReflectionClass(PhpVersion::class);
        // @todo check
        if (\in_array($phpVersion, $phpVersionReflectionClass->getConstants(), \true)) {
            return;
        }
        // ensure cast to (string) first to allow string like "8.0" value to be converted to the int value
        /** @var PhpVersion::* $phpVersion */
        $this->phpVersion = $phpVersion;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove unneeded PHP_VERSION_ID conditional checks', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
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
, [PhpVersion::PHP_80])]);
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
     * @return null|If_|Stmt[]
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
     * @return null|If_|Stmt[]
     */
    private function processSmaller(ConstFetch $constFetch, Smaller $smaller, If_ $if)
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
     * @return null|If_|Stmt[]
     */
    private function processGreaterOrEqual(ConstFetch $constFetch, GreaterOrEqual $greaterOrEqual, If_ $if)
    {
        if ($greaterOrEqual->left === $constFetch) {
            return $this->processGreaterOrEqualLeft($greaterOrEqual, $if);
        }
        if ($greaterOrEqual->right === $constFetch) {
            return $this->processGreaterOrEqualRight($greaterOrEqual, $if);
        }
        return null;
    }
    private function processSmallerLeft(Smaller $smaller, If_ $if) : ?If_
    {
        $value = $smaller->right;
        if (!$value instanceof LNumber) {
            return null;
        }
        if ($this->phpVersion >= $value->value) {
            $this->removeNode($if);
            return $if;
        }
        return null;
    }
    /**
     * @return null|If_|Stmt[]
     */
    private function processSmallerRight(Smaller $smaller, If_ $if)
    {
        $value = $smaller->left;
        if (!$value instanceof LNumber) {
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
     * @return null|If_|Stmt[]
     */
    private function processGreaterOrEqualLeft(GreaterOrEqual $greaterOrEqual, If_ $if)
    {
        $value = $greaterOrEqual->right;
        if (!$value instanceof LNumber) {
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
    private function processGreaterOrEqualRight(GreaterOrEqual $greaterOrEqual, If_ $if) : ?If_
    {
        $value = $greaterOrEqual->left;
        if (!$value instanceof LNumber) {
            return null;
        }
        if ($this->phpVersion >= $value->value) {
            $this->removeNode($if);
            return $if;
        }
        return null;
    }
    /**
     * @return null|If_|Stmt[]
     */
    private function processGreater(ConstFetch $constFetch, Greater $greater, If_ $if)
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
     * @return null|If_|Stmt[]
     */
    private function processGreaterLeft(Greater $greater, If_ $if)
    {
        $value = $greater->right;
        if (!$value instanceof LNumber) {
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
    private function processGreaterRight(Greater $greater, If_ $if) : ?If_
    {
        $value = $greater->left;
        if (!$value instanceof LNumber) {
            return null;
        }
        if ($this->phpVersion >= $value->value) {
            $this->removeNode($if);
            return $if;
        }
        return null;
    }
    /**
     * @return null|If_|Stmt[]
     */
    private function refactorConstFetch(ConstFetch $constFetch, If_ $if, BinaryOp $binaryOp)
    {
        if ($binaryOp instanceof Smaller) {
            return $this->processSmaller($constFetch, $binaryOp, $if);
        }
        if ($binaryOp instanceof GreaterOrEqual) {
            return $this->processGreaterOrEqual($constFetch, $binaryOp, $if);
        }
        if ($binaryOp instanceof Greater) {
            return $this->processGreater($constFetch, $binaryOp, $if);
        }
        return null;
    }
}
