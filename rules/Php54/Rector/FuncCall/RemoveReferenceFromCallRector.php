<?php

declare (strict_types=1);
namespace Rector\Php54\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php54\Rector\FuncCall\RemoveReferenceFromCallRector\RemoveReferenceFromCallRectorTest
 */
final class RemoveReferenceFromCallRector extends \Rector\Core\Rector\AbstractRector implements \Rector\VersionBonding\Contract\MinPhpVersionInterface
{
    public function provideMinPhpVersion() : int
    {
        return \Rector\Core\ValueObject\PhpVersionFeature::NO_REFERENCE_IN_ARG;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Remove & from function and method calls', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function run($one)
    {
        return strlen(&$one);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run($one)
    {
        return strlen($one);
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
        return [\PhpParser\Node\Expr\FuncCall::class];
    }
    /**
     * @param FuncCall $node
     * @return \PhpParser\Node\Expr\FuncCall|null
     */
    public function refactor(\PhpParser\Node $node)
    {
        $hasChanged = \false;
        foreach ($node->args as $nodeArg) {
            if (!$nodeArg instanceof \PhpParser\Node\Arg) {
                continue;
            }
            if (!$nodeArg->byRef) {
                continue;
            }
            $nodeArg->byRef = \false;
            $hasChanged = \true;
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
}
