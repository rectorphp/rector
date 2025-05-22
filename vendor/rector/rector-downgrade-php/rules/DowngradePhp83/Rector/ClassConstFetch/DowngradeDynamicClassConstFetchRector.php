<?php

declare (strict_types=1);
namespace Rector\DowngradePhp83\Rector\ClassConstFetch;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\String_;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/dynamic_class_constant_fetch
 *
 * @see \Rector\Tests\DowngradePhp83\Rector\ClassConstFetch\DowngradeDynamicClassConstFetchRector\DowngradeDynamicClassConstFetchRectorTest
 */
final class DowngradeDynamicClassConstFetchRector extends AbstractRector
{
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [ClassConstFetch::class];
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change dynamic class const fetch Example::{$constName} to constant(Example::class . \'::\' . $constName)', [new CodeSample(<<<'CODE_SAMPLE'
$value = Example::{$constName};
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$value = constant(Example::class . '::' . $constName);
CODE_SAMPLE
)]);
    }
    /**
     * @param ClassConstFetch $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node->name instanceof Identifier) {
            return null;
        }
        return $this->nodeFactory->createFuncCall('constant', [new Concat(new Concat(new ClassConstFetch($node->class, new Identifier('class')), new String_('::')), $node->name)]);
    }
}
