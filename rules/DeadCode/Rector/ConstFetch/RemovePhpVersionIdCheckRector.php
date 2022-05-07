<?php

declare(strict_types=1);

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
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Webmozart\Assert\Assert;

/**
 * @see \Rector\Tests\DeadCode\Rector\ConstFetch\RemovePhpVersionIdCheckRector\RemovePhpVersionIdCheckRectorTest
 */
final class RemovePhpVersionIdCheckRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var PhpVersion::*|null
     */
    private int | null $phpVersion = null;

    public function __construct(
        private readonly PhpVersionProvider $phpVersionProvider,
    ) {
    }

    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration): void
    {
        $phpVersion = $configuration[0];
        Assert::integer($phpVersion);
        PhpVersion::assertValidValue($phpVersion);

        // ensure cast to (string) first to allow string like "8.0" value to be converted to the int value
        /** @var PhpVersion::* $phpVersion */
        $this->phpVersion = $phpVersion;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Remove unneeded PHP_VERSION_ID conditional checks',
            [
                new ConfiguredCodeSample(
                    <<<'CODE_SAMPLE'
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
,
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        echo 'do something';
    }
}
CODE_SAMPLE
,
                    [PhpVersion::PHP_80]
                ),
            ],
        );
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [If_::class];
    }

    /**
     * @param If_ $node
     * @return Stmt[]|null
     */
    public function refactor(Node $node): ?array
    {
        /**
         * $this->phpVersionProvider->provide() fallback is here as $currentFileProvider must be accessed after initialization
         */
        if ($this->phpVersion === null) {
            $this->phpVersion = $this->phpVersionProvider->provide();
        }

        if (! $node->cond instanceof BinaryOp) {
            return null;
        }

        $binaryOp = $node->cond;
        if ($binaryOp->left instanceof ConstFetch && $this->isName($binaryOp->left->name, 'PHP_VERSION_ID')) {
            return $this->refactorConstFetch($binaryOp->left, $node, $binaryOp);
        }

        if (! $binaryOp->right instanceof ConstFetch) {
            return null;
        }

        if (! $this->isName($binaryOp->right->name, 'PHP_VERSION_ID')) {
            return null;
        }

        return $this->refactorConstFetch($binaryOp->right, $node, $binaryOp);
    }

    /**
     * @return Stmt[]|null
     */
    private function processSmaller(ConstFetch $constFetch, Smaller $smaller, If_ $if): ?array
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
     * @return Stmt[]|null
     */
    private function processGreaterOrEqual(
        ConstFetch $constFetch,
        GreaterOrEqual $greaterOrEqual,
        If_ $if,
    ): ?array {
        if ($greaterOrEqual->left === $constFetch) {
            return $this->processGreaterOrEqualLeft($greaterOrEqual, $if);
        }

        if ($greaterOrEqual->right === $constFetch) {
            return $this->processGreaterOrEqualRight($greaterOrEqual, $if);
        }

        return null;
    }

    /**
     * @return null
     */
    private function processSmallerLeft(Smaller $smaller, If_ $if)
    {
        $value = $smaller->right;
        if (! $value instanceof LNumber) {
            return null;
        }

        if ($this->phpVersion >= $value->value) {
            $this->removeNode($if);
        }

        return null;
    }

    /**
     * @return Stmt[]|null
     */
    private function processSmallerRight(Smaller $smaller, If_ $if): ?array
    {
        $value = $smaller->left;
        if (! $value instanceof LNumber) {
            return null;
        }

        if ($this->phpVersion >= $value->value) {
            return $if->stmts;
        }

        return null;
    }

    /**
     * @return Stmt[]|null
     */
    private function processGreaterOrEqualLeft(GreaterOrEqual $greaterOrEqual, If_ $if): array|null
    {
        $value = $greaterOrEqual->right;
        if (! $value instanceof LNumber) {
            return null;
        }

        if ($this->phpVersion >= $value->value) {
            return $if->stmts;
        }

        return null;
    }

    /**
     * @return null
     */
    private function processGreaterOrEqualRight(GreaterOrEqual $greaterOrEqual, If_ $if)
    {
        $value = $greaterOrEqual->left;
        if (! $value instanceof LNumber) {
            return null;
        }

        if ($this->phpVersion >= $value->value) {
            $this->removeNode($if);
        }

        return null;
    }

    /**
     * @return Stmt[]|null
     */
    private function processGreater(ConstFetch $constFetch, Greater $greater, If_ $if): ?array
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
     * @return Stmt[]|null
     */
    private function processGreaterLeft(Greater $greater, If_ $if): ?array
    {
        $value = $greater->right;
        if (! $value instanceof LNumber) {
            return null;
        }

        if ($this->phpVersion >= $value->value) {
            return $if->stmts;
        }

        return null;
    }

    /**
     * @return null
     */
    private function processGreaterRight(Greater $greater, If_ $if)
    {
        $value = $greater->left;
        if (! $value instanceof LNumber) {
            return null;
        }

        if ($this->phpVersion >= $value->value) {
            $this->removeNode($if);
        }

        return null;
    }

    /**
     * @return Stmt[]|null
     */
    private function refactorConstFetch(ConstFetch $constFetch, If_ $if, BinaryOp $binaryOp): ?array
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
