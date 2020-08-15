<?php

declare(strict_types=1);

namespace Rector\Order\Rector\ClassLike;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Trait_;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Order\StmtVisibilitySorter;

/**
 * @see \Rector\Order\Tests\Rector\ClassLike\OrderConstantsByVisibilityRector\OrderConstantsByVisibilityRectorTest
 */
final class OrderConstantsByVisibilityRector extends AbstractRector
{
    /**
     * @var StmtVisibilitySorter
     */
    private $stmtVisibilitySorter;

    public function __construct(StmtVisibilitySorter $stmtVisibilitySorter)
    {
        $this->stmtVisibilitySorter = $stmtVisibilitySorter;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Orders constants by visibility', [
            new CodeSample(
                <<<'PHP'
final class SomeClass
{
    private const PRIVATE_CONST = 'private';
    protected const PROTECTED_CONST = 'protected';
    public const PUBLIC_CONST = 'public';
}
PHP

                ,
                <<<'PHP'
final class SomeClass
{
    public const PUBLIC_CONST = 'public';
    protected const PROTECTED_CONST = 'protected';
    private const PRIVATE_CONST = 'private';
}
PHP

            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [ClassLike::class];
    }

    /**
     * @param ClassLike $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof Interface_) {
            return null;
        }

        if ($node instanceof Trait_) {
            return null;
        }

        $node->stmts = $this->stmtVisibilitySorter->sortConstants($node->stmts);

        return $node;
    }
}
