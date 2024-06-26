<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Cast\Int_;
use PhpParser\Node\Expr\FuncCall;
use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @deprecated Since 1.1.2 as no clear performance difference and both are equivalent.
 */
final class IntvalToTypeCastRector extends AbstractRector implements DeprecatedInterface
{
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    public function __construct(ValueResolver $valueResolver)
    {
        $this->valueResolver = $valueResolver;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change intval() to faster and readable (int) $value', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run($value)
    {
        return intval($value);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run($value)
    {
        return (int) $value;
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
        if (!$this->isName($node, 'intval')) {
            return null;
        }
        if (isset($node->args[1]) && $node->args[1] instanceof Arg) {
            $secondArgumentValue = $this->valueResolver->getValue($node->args[1]->value);
            // default value
            if ($secondArgumentValue !== 10) {
                return null;
            }
        }
        if ($node->isFirstClassCallable()) {
            return null;
        }
        if (!isset($node->getArgs()[0])) {
            return null;
        }
        return new Int_($node->getArgs()[0]->value);
    }
}
