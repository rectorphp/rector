<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\FuncCall\RemoveFilterVarOnExactTypeRector\RemoveFilterVarOnExactTypeRectorTest
 */
final class RemoveFilterVarOnExactTypeRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Removes filter_var() calls with exact type', [new CodeSample(<<<'CODE_SAMPLE'
function (int $value) {
    $result = filter_var($value, FILTER_VALIDATE_INT);
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
function (int $value) {
    $result = $value;
}
CODE_SAMPLE
)]);
    }
    public function getNodeTypes() : array
    {
        return [FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node->isFirstClassCallable()) {
            return null;
        }
        if (!$this->isName($node, 'filter_var')) {
            return null;
        }
        // we need exact 2nd arg to assess value type
        if (\count($node->getArgs()) !== 2) {
            return null;
        }
        $firstArgValue = $node->getArgs()[0]->value;
        $secondArgValue = $node->getArgs()[1]->value;
        if (!$secondArgValue instanceof ConstFetch) {
            return null;
        }
        $constantFilterName = $secondArgValue->name->toString();
        $valueType = $this->nodeTypeResolver->getNativeType($firstArgValue);
        if ($constantFilterName === 'FILTER_VALIDATE_INT' && $valueType->isInteger()->yes()) {
            return $firstArgValue;
        }
        if ($constantFilterName === 'FILTER_VALIDATE_FLOAT' && $valueType->isFloat()->yes()) {
            return $firstArgValue;
        }
        if (\in_array($constantFilterName, ['FILTER_VALIDATE_BOOLEAN', 'FILTER_VALIDATE_BOOL']) && $valueType->isBoolean()->yes()) {
            return $firstArgValue;
        }
        return null;
    }
}
