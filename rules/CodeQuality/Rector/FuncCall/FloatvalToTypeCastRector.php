<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Cast\Double;
use PhpParser\Node\Expr\FuncCall;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\FuncCall\FloatvalToTypeCastRector\FloatvalToTypeCastRectorTest
 */
final class FloatvalToTypeCastRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private const VAL_FUNCTION_NAMES = ['floatval', 'doubleval'];
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change floatval() and doubleval() to faster and readable (float) $value', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run($value)
    {
        $a = floatval($value);
        $b = doubleval($value);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run($value)
    {
        $a = (float) $value;
        $b = (float) $value;
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
        return [FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        $methodName = $this->getName($node);
        if ($methodName === null) {
            return null;
        }
        if (!\in_array($methodName, self::VAL_FUNCTION_NAMES, \true)) {
            return null;
        }
        if (!isset($node->args[0])) {
            return null;
        }
        if (!$node->args[0] instanceof Arg) {
            return null;
        }
        $double = new Double($node->args[0]->value);
        $double->setAttribute(AttributeKey::KIND, Double::KIND_FLOAT);
        return $double;
    }
}
