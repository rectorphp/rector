<?php

declare(strict_types=1);

namespace Rector\Order\Rector\ClassLike;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Interface_;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Order\StmtVisibilitySorter;

/**
 * @see \Rector\Order\Tests\Rector\ClassLike\OrderPropertiesByVisibilityRector\OrderPropertiesByVisibilityRectorTest
 */
final class OrderPropertiesByVisibilityRector extends AbstractRector
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
        return new RectorDefinition('Orders properties by visibility', [
            new CodeSample(
                <<<'PHP'
final class SomeClass
{
    protected $protectedProperty;
    private $privateProperty;
    public $publicProperty;
}
PHP

                ,
                <<<'PHP'
final class SomeClass
{
    public $publicProperty;
    protected $protectedProperty;
    private $privateProperty;
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

        $node->stmts = $this->stmtVisibilitySorter->sortProperties($node->stmts);
        return $node;
    }
}
