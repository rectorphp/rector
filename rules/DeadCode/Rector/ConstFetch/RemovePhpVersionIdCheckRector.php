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
use PhpParser\Node\Stmt\If_;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\Util\PhpVersionFactory;
use Rector\Core\ValueObject\PhpVersion;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Tests\DeadCode\Rector\ConstFetch\RemovePhpVersionIdCheckRector\RemovePhpVersionIdCheckRectorTest
 */
final class RemovePhpVersionIdCheckRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const PHP_VERSION_CONSTRAINT = 'phpVersionConstraint';

    private string | int | null $phpVersionConstraint;

    public function __construct(
        private PhpVersionFactory $phpVersionFactory
    ) {
    }

    /**
     * @param array<string, int|string> $configuration
     */
    public function configure(array $configuration): void
    {
        $this->phpVersionConstraint = $configuration[self::PHP_VERSION_CONSTRAINT] ?? null;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        $exampleConfiguration = [
            self::PHP_VERSION_CONSTRAINT => PhpVersion::PHP_80,
        ];
        return new RuleDefinition(
            'Remove unneded PHP_VERSION_ID check',
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
                    $exampleConfiguration
                ),
            ],
        );
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [ConstFetch::class];
    }

    /**
     * @param ConstFetch $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isName($node, 'PHP_VERSION_ID')) {
            return null;
        }

        /**
         * $this->phpVersionProvider->provide() fallback is here as $currentFileProvider must be accessed after initialization
         */
        $phpVersionConstraint = $this->phpVersionConstraint ?? $this->phpVersionProvider->provide();

        // ensure cast to (string) first to allow string like "8.0" value to be converted to the int value
        $this->phpVersionConstraint = $this->phpVersionFactory->createIntVersion((string) $phpVersionConstraint);

        $if = $this->betterNodeFinder->findParentType($node, If_::class);
        $parent = $node->getAttribute(AttributeKey::PARENT_NODE);
        if ($this->shouldSkip($node, $if, $parent)) {
            return null;
        }

        /** @var If_ $if */
        if ($parent instanceof Smaller) {
            return $this->processSmaller($node, $parent, $if);
        }

        if ($parent instanceof GreaterOrEqual) {
            return $this->processGreaterOrEqual($node, $parent, $if);
        }

        if ($parent instanceof Greater) {
            return $this->processGreater($node, $parent, $if);
        }

        return null;
    }

    private function shouldSkip(ConstFetch $constFetch, ?If_ $if, ?Node $node): bool
    {
        $if = $this->betterNodeFinder->findParentType($constFetch, If_::class);
        if (! $if instanceof If_) {
            return true;
        }

        $node = $constFetch->getAttribute(AttributeKey::PARENT_NODE);
        if (! $node instanceof BinaryOp) {
            return true;
        }

        return $if->cond !== $node;
    }

    private function processSmaller(ConstFetch $constFetch, Smaller $smaller, If_ $if): ?ConstFetch
    {
        if ($smaller->left === $constFetch) {
            return $this->processSmallerLeft($constFetch, $smaller, $if);
        }

        if ($smaller->right === $constFetch) {
            return $this->processSmallerRight($constFetch, $smaller, $if);
        }

        return null;
    }

    private function processGreaterOrEqual(ConstFetch $constFetch, GreaterOrEqual $greaterOrEqual, If_ $if): ?ConstFetch
    {
        if ($greaterOrEqual->left === $constFetch) {
            return $this->processGreaterOrEqualLeft($constFetch, $greaterOrEqual, $if);
        }

        if ($greaterOrEqual->right === $constFetch) {
            return $this->processGreaterOrEqualRight($constFetch, $greaterOrEqual, $if);
        }

        return null;
    }

    private function processSmallerLeft(ConstFetch $constFetch, Smaller $smaller, If_ $if): ?ConstFetch
    {
        $value = $smaller->right;
        if (! $value instanceof LNumber) {
            return null;
        }

        if ($this->phpVersionConstraint >= $value->value) {
            $this->removeNode($if);
        }

        return $constFetch;
    }

    private function processSmallerRight(ConstFetch $constFetch, Smaller $smaller, If_ $if): ?ConstFetch
    {
        $value = $smaller->left;
        if (! $value instanceof LNumber) {
            return null;
        }

        if ($this->phpVersionConstraint >= $value->value) {
            $this->addNodesBeforeNode($if->stmts, $if);
            $this->removeNode($if);
        }

        return $constFetch;
    }

    private function processGreaterOrEqualLeft(
        ConstFetch $constFetch,
        GreaterOrEqual $greaterOrEqual,
        If_ $if
    ): ?ConstFetch
    {
        $value = $greaterOrEqual->right;
        if (! $value instanceof LNumber) {
            return null;
        }

        if ($this->phpVersionConstraint >= $value->value) {
            $this->addNodesBeforeNode($if->stmts, $if);
            $this->removeNode($if);
        }

        return $constFetch;
    }

    private function processGreaterOrEqualRight(
        ConstFetch $constFetch,
        GreaterOrEqual $greaterOrEqual,
        If_ $if
    ): ?ConstFetch
    {
        $value = $greaterOrEqual->left;
        if (! $value instanceof LNumber) {
            return null;
        }

        if ($this->phpVersionConstraint >= $value->value) {
            $this->removeNode($if);
        }

        return $constFetch;
    }

    private function processGreater(ConstFetch $constFetch, Greater $greater, If_ $if): ?ConstFetch
    {
        if ($greater->left === $constFetch) {
            return $this->processGreaterLeft($constFetch, $greater, $if);
        }

        if ($greater->right === $constFetch) {
            return $this->processGreaterRight($constFetch, $greater, $if);
        }

        return null;
    }

    private function processGreaterLeft(ConstFetch $constFetch, Greater $greater, If_ $if): ?ConstFetch
    {
        $value = $greater->right;
        if (! $value instanceof LNumber) {
            return null;
        }

        if ($this->phpVersionConstraint >= $value->value) {
            $this->addNodesBeforeNode($if->stmts, $if);
            $this->removeNode($if);
        }

        return $constFetch;
    }

    private function processGreaterRight(ConstFetch $constFetch, Greater $greater, If_ $if): ?ConstFetch
    {
        $value = $greater->left;
        if (! $value instanceof LNumber) {
            return null;
        }

        if ($this->phpVersionConstraint >= $value->value) {
            $this->removeNode($if);
        }

        return $constFetch;
    }
}
