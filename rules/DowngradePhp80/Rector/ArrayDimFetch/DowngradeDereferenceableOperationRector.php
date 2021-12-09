<?php

declare (strict_types=1);
namespace Rector\DowngradePhp80\Rector\ArrayDimFetch;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Scalar\Encapsed;
use PhpParser\Node\Scalar\MagicConst;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/variable_syntax_tweaks
 *
 * @see \Rector\Tests\DowngradePhp80\Rector\ArrayDimFetch\DowngradeDereferenceableOperationRector\DowngradeDereferenceableOperationRectorTest
 */
final class DowngradeDereferenceableOperationRector extends \Rector\Core\Rector\AbstractRector
{
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Add parentheses around non-dereferenceable expressions.', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
function getFirstChar(string $str, string $suffix = '')
{
    return "$str$suffix"[0];
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
function getFirstChar(string $str, string $suffix = '')
{
    return ("$str$suffix")[0];
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\ArrayDimFetch::class];
    }
    /**
     * @param ArrayDimFetch $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        $node->var->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::WRAPPED_IN_PARENTHESES, \true);
        return $node;
    }
    private function shouldSkip(\PhpParser\Node\Expr\ArrayDimFetch $arrayDimFetch) : bool
    {
        if ($arrayDimFetch->dim === null) {
            return \true;
        }
        if ($arrayDimFetch->var instanceof \PhpParser\Node\Scalar\Encapsed) {
            return $this->hasParentheses($arrayDimFetch);
        }
        if ($arrayDimFetch->var instanceof \PhpParser\Node\Scalar\MagicConst) {
            return $this->hasParentheses($arrayDimFetch);
        }
        return \true;
    }
    private function hasParentheses(\PhpParser\Node\Expr\ArrayDimFetch $arrayDimFetch) : bool
    {
        $wrappedInParentheses = $arrayDimFetch->var->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::WRAPPED_IN_PARENTHESES);
        if ($wrappedInParentheses === \true) {
            return \true;
        }
        \assert($arrayDimFetch->dim !== null);
        // already checked in shouldSkip()
        $oldTokens = $this->file->getOldTokens();
        $varEndTokenPos = $arrayDimFetch->var->getEndTokenPos();
        $dimStartTokenPos = $arrayDimFetch->dim->getStartTokenPos();
        for ($i = $varEndTokenPos + 1; $i < $dimStartTokenPos; ++$i) {
            if (!isset($oldTokens[$i])) {
                continue;
            }
            if ($oldTokens[$i] !== ')') {
                continue;
            }
            return \true;
        }
        return \false;
    }
}
