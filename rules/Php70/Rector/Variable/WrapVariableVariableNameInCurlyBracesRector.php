<?php

declare (strict_types=1);
namespace Rector\Php70\Rector\Variable;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php70\Rector\Variable\WrapVariableVariableNameInCurlyBracesRector\WrapVariableVariableNameInCurlyBracesRectorTest
 * @changelog https://www.php.net/manual/en/language.variables.variable.php
 */
final class WrapVariableVariableNameInCurlyBracesRector extends \Rector\Core\Rector\AbstractRector implements \Rector\VersionBonding\Contract\MinPhpVersionInterface
{
    public function provideMinPhpVersion() : int
    {
        return \Rector\Core\ValueObject\PhpVersionFeature::WRAP_VARIABLE_VARIABLE;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Ensure variable variables are wrapped in curly braces', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
function run($foo)
{
    global $$foo->bar;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
function run($foo)
{
    global ${$foo->bar};
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\Variable::class];
    }
    /**
     * @param Variable $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $nodeName = $node->name;
        if (!$nodeName instanceof \PhpParser\Node\Expr\PropertyFetch && !$nodeName instanceof \PhpParser\Node\Expr\Variable) {
            return null;
        }
        if ($node->getEndTokenPos() !== $nodeName->getEndTokenPos()) {
            return null;
        }
        if ($nodeName instanceof \PhpParser\Node\Expr\PropertyFetch) {
            return new \PhpParser\Node\Expr\Variable(new \PhpParser\Node\Expr\PropertyFetch($nodeName->var, $nodeName->name));
        }
        return new \PhpParser\Node\Expr\Variable(new \PhpParser\Node\Expr\Variable($nodeName->name));
    }
}
