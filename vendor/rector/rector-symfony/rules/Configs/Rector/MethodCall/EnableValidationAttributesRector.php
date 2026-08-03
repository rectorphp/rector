<?php

declare (strict_types=1);
namespace Rector\Symfony\Configs\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\ArrayItem;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Name;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Symfony\Tests\Configs\Rector\MethodCall\EnableValidationAttributesRector\EnableValidationAttributesRectorTest
 */
final class EnableValidationAttributesRector extends AbstractRector
{
    /**
     * @readonly
     */
    private ValueResolver $valueResolver;
    public function __construct(ValueResolver $valueResolver)
    {
        $this->valueResolver = $valueResolver;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Enable "framework.validation.enable_attributes" config, to load validation rules from attributes', [new CodeSample(<<<'CODE_SAMPLE'
$container->loadFromExtension('framework', [
    'validation' => [
        'enable_attributes' => false,
    ],
]);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$container->loadFromExtension('framework', [
    'validation' => [
        'enable_attributes' => true,
    ],
]);
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (!$this->isName($node->name, 'loadFromExtension')) {
            return null;
        }
        $args = $node->getArgs();
        if (count($args) < 2) {
            return null;
        }
        if (!$this->valueResolver->isValue($args[0]->value, 'framework')) {
            return null;
        }
        $configArray = $args[1]->value;
        if (!$configArray instanceof Array_) {
            return null;
        }
        $validationArrayItem = $this->matchArrayItemByKey($configArray, 'validation');
        if (!$validationArrayItem instanceof ArrayItem) {
            return null;
        }
        if (!$validationArrayItem->value instanceof Array_) {
            return null;
        }
        $enableAttributesArrayItem = $this->matchArrayItemByKey($validationArrayItem->value, 'enable_attributes');
        if (!$enableAttributesArrayItem instanceof ArrayItem) {
            return null;
        }
        if (!$this->valueResolver->isFalse($enableAttributesArrayItem->value)) {
            return null;
        }
        $enableAttributesArrayItem->value = new ConstFetch(new Name('true'));
        return $node;
    }
    private function matchArrayItemByKey(Array_ $array, string $keyName): ?ArrayItem
    {
        foreach ($array->items as $arrayItem) {
            if (!$arrayItem instanceof ArrayItem) {
                continue;
            }
            if (!$arrayItem->key instanceof Expr) {
                continue;
            }
            if (!$this->valueResolver->isValue($arrayItem->key, $keyName)) {
                continue;
            }
            return $arrayItem;
        }
        return null;
    }
}
