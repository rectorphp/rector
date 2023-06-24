<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony25\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;
use PHPStan\Type\ObjectType;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://github.com/symfony/symfony/commit/feea36df99205fd12be52515edeb10fc18523232
 * @changelog https://github.com/symfony/symfony-docs/pull/3461
 * @changelog https://github.com/symfony/symfony/issues/7148
 *
 * @see \Rector\Symfony\Tests\Symfony25\Rector\Array_\MaxLengthSymfonyFormOptionToAttrRector\MaxLengthSymfonyFormOptionToAttrRectorTest
 */
final class MaxLengthSymfonyFormOptionToAttrRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change form option "max_length" to a form "attr" > "max_length"', [new CodeSample(<<<'CODE_SAMPLE'
$formBuilder = new Symfony\Component\Form\FormBuilder();

$form = $formBuilder->create('name', 'text', [
    'max_length' => 123,
]);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$formBuilder = new Symfony\Component\Form\FormBuilder();

$form = $formBuilder->create('name', 'text', [
    'attr' => ['maxlength' => 123],
]);
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->isFormCreateMethodCallMatch($node)) {
            return null;
        }
        $optionsArg = $node->getArgs()[2] ?? null;
        if (!$optionsArg instanceof Arg) {
            return null;
        }
        if (!$optionsArg->value instanceof Array_) {
            return null;
        }
        $optionsArray = $optionsArg->value;
        $itemToAddToAttrs = null;
        foreach ($optionsArray->items as $arrayKey => $arrayItem) {
            if (!$arrayItem instanceof ArrayItem) {
                continue;
            }
            if (!$arrayItem->key instanceof String_) {
                continue;
            }
            if (!$this->valueResolver->isValue($arrayItem->key, 'max_length')) {
                continue;
            }
            unset($optionsArray->items[$arrayKey]);
            $itemToAddToAttrs = $arrayItem;
            break;
        }
        if (!$itemToAddToAttrs instanceof ArrayItem) {
            return null;
        }
        $this->addArrayItemToAttrsItemOrCreateOne($optionsArray, $itemToAddToAttrs);
        return $node;
    }
    private function matchAttrArrayItem(Array_ $array) : ?ArrayItem
    {
        foreach ($array->items as $arrayItem) {
            if (!$arrayItem instanceof ArrayItem) {
                continue;
            }
            if (!$arrayItem->key instanceof String_) {
                continue;
            }
            if (!$this->valueResolver->isValue($arrayItem->key, 'attrs')) {
                continue;
            }
            return $arrayItem;
        }
        return null;
    }
    private function addArrayItemToAttrsItemOrCreateOne(Array_ $array, ArrayItem $arrayItem) : Array_
    {
        // rename
        $arrayItem->key = new String_('maxlength');
        $attrArrayItem = $this->matchAttrArrayItem($array);
        if ($attrArrayItem instanceof ArrayItem) {
            if (!$attrArrayItem->value instanceof Array_) {
                throw new ShouldNotHappenException();
            }
            $attrArrayItem->value->items[] = $arrayItem;
            return $array;
        }
        $array->items[] = new ArrayItem(new Array_([$arrayItem]), new String_('attr'));
        return $array;
    }
    private function isFormCreateMethodCallMatch(MethodCall $methodCall) : bool
    {
        if (!$this->isNames($methodCall->name, ['create', 'add'])) {
            return \false;
        }
        return $this->isObjectType($methodCall->var, new ObjectType('Symfony\\Component\\Form\\FormFactoryInterface')) || $this->isObjectType($methodCall->var, new ObjectType('Symfony\\Component\\Form\\FormBuilderInterface'));
    }
}
