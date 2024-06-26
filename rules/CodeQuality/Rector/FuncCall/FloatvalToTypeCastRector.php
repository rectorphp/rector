<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\Cast\Double;
use PhpParser\Node\Expr\FuncCall;
use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @deprecated Since 1.1.2 as no clear performance difference and both are equivalent.
 */
final class FloatvalToTypeCastRector extends AbstractRector implements DeprecatedInterface
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
        if ($node->isFirstClassCallable()) {
            return null;
        }
        $firstArg = $node->getArgs()[0];
        $double = new Double($firstArg->value);
        $double->setAttribute(AttributeKey::KIND, Double::KIND_FLOAT);
        return $double;
    }
}
