<?php

declare (strict_types=1);
namespace Rector\DowngradePhp54\Rector\Closure;

use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DowngradePhp54\Rector\Closure\DowngradeStaticClosureRector\DowngradeStaticClosureRectorTest
 */
final class DowngradeStaticClosureRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove static from closure', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function run()
    {
        return static function () {
            return true;
        };
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run()
    {
        return function () {
            return true;
        };
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
        return [Closure::class];
    }
    /**
     * @param Closure $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$node->static) {
            return null;
        }
        $node->static = \false;
        return $node;
    }
}
