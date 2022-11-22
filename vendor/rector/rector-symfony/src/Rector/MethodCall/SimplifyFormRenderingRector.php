<?php

declare (strict_types=1);
namespace Rector\Symfony\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Symfony\Tests\Rector\MethodCall\SimplifyFormRenderingRector\SimplifyFormRenderingRectorTest
 */
final class SimplifyFormRenderingRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Symplify form rendering by not calling `->createView()` on `render` function', [new CodeSample(<<<'CODE_SAMPLE'
class ReplaceFormCreateViewFunctionCall extends AbstractController
{
    public function form(): Response
    {
        return $this->render('form.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class ReplaceFormCreateViewFunctionCall extends AbstractController
{
    public function form(): Response
    {
        return $this->render('form.html.twig', [
            'form' => $form,
        ]);
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
        return [MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->isObjectType($node->var, new ObjectType('Symfony\\Bundle\\FrameworkBundle\\Controller\\AbstractController'))) {
            return null;
        }
        if ($node->isFirstClassCallable()) {
            return null;
        }
        if (!$this->isName($node->name, 'render')) {
            return null;
        }
        if (!isset($node->args[1])) {
            return null;
        }
        /** @var Arg $arg */
        $arg = $node->args[1];
        if (!$arg->value instanceof Array_) {
            return null;
        }
        $methodCallOrNull = $this->processRemoveCreateView($arg->value->items);
        if ($methodCallOrNull === null) {
            return null;
        }
        $arg->value->items = $methodCallOrNull;
        return $node;
    }
    /**
     * @param ArrayItem[]|null[] $arrayItems
     *
     * @return array<ArrayItem|null>|null
     */
    private function processRemoveCreateView(array $arrayItems) : ?array
    {
        $replaced = \false;
        foreach ($arrayItems as $arrayItem) {
            if (!$arrayItem instanceof ArrayItem) {
                continue;
            }
            if (!$arrayItem->value instanceof MethodCall) {
                continue;
            }
            if (!$this->isObjectType($arrayItem->value->var, new ObjectType('Symfony\\Component\\Form\\FormInterface'))) {
                continue;
            }
            $replaced = \true;
            $arrayItem->value = $arrayItem->value->var;
        }
        if (!$replaced) {
            return null;
        }
        return $arrayItems;
    }
}
