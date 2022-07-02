<?php

declare (strict_types=1);
namespace Rector\Laravel\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;
use Rector\Core\NodeManipulator\ArrayManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\Laravel\NodeAnalyzer\LumenRouteRegisteringMethodAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Laravel\Tests\Rector\MethodCall\LumenRoutesStringMiddlewareToArrayRector\LumenRoutesStringMiddlewareToArrayRectorTest
 */
final class LumenRoutesStringMiddlewareToArrayRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\ArrayManipulator
     */
    private $arrayManipulator;
    /**
     * @readonly
     * @var \Rector\Laravel\NodeAnalyzer\LumenRouteRegisteringMethodAnalyzer
     */
    private $lumenRouteRegisteringMethodAnalyzer;
    public function __construct(ArrayManipulator $arrayManipulator, LumenRouteRegisteringMethodAnalyzer $lumenRouteRegisteringMethodAnalyzer)
    {
        $this->arrayManipulator = $arrayManipulator;
        $this->lumenRouteRegisteringMethodAnalyzer = $lumenRouteRegisteringMethodAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Changes middlewares from rule definitions from string to array notation.', [new CodeSample(<<<'CODE_SAMPLE'
$router->get('/user', ['middleware => 'test']);
$router->post('/user', ['middleware => 'test|authentication']);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$router->get('/user', ['middleware => ['test']]);
$router->post('/user', ['middleware => ['test', 'authentication']]);
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
    public function refactor(Node $node) : ?Node
    {
        if (!$node instanceof MethodCall) {
            return null;
        }
        if (!$this->lumenRouteRegisteringMethodAnalyzer->isLumenRoutingClass($node)) {
            return null;
        }
        $array = $this->getRouterMethodAttributes($node);
        if (!$array instanceof Array_) {
            return null;
        }
        $arrayItem = $this->findItemInArrayByKey($array, 'middleware');
        if (!$arrayItem instanceof ArrayItem) {
            return null;
        }
        $middlewareValue = $arrayItem->value;
        if (!$middlewareValue instanceof String_) {
            return null;
        }
        /** @var string $middlewareString */
        $middlewareString = $middlewareValue->value;
        $splitMiddleware = \explode('|', $middlewareString);
        $newMiddlewareArray = new Array_([]);
        foreach ($splitMiddleware as $singleSplitMiddleware) {
            $newMiddlewareArray->items[] = new ArrayItem(new String_($singleSplitMiddleware));
        }
        $this->replaceItemInArrayByKey($array, new ArrayItem($newMiddlewareArray, new String_('middleware')), 'middleware');
        return $node;
    }
    private function getRouterMethodAttributes(MethodCall $methodCall) : ?Array_
    {
        $attributes = null;
        if ($this->lumenRouteRegisteringMethodAnalyzer->isRoutesRegisterGroup($methodCall->name)) {
            $attributes = $methodCall->getArgs()[0]->value;
        }
        if ($this->lumenRouteRegisteringMethodAnalyzer->isRoutesRegisterRoute($methodCall->name)) {
            $attributes = $methodCall->getArgs()[1]->value;
        }
        if (!$attributes instanceof Array_) {
            return null;
        }
        return $attributes;
    }
    private function findItemInArrayByKey(Array_ $array, string $keyName) : ?ArrayItem
    {
        foreach ($array->items as $i => $item) {
            if ($item === null) {
                continue;
            }
            if (!$this->arrayManipulator->hasKeyName($item, $keyName)) {
                continue;
            }
            $foundArrayItem = $array->items[$i];
            if (!$foundArrayItem instanceof ArrayItem) {
                continue;
            }
            return $item;
        }
        return null;
    }
    private function replaceItemInArrayByKey(Array_ $array, ArrayItem $arrayItem, string $keyName) : void
    {
        foreach ($array->items as $i => $item) {
            if ($item === null) {
                continue;
            }
            if (!$this->arrayManipulator->hasKeyName($item, $keyName)) {
                continue;
            }
            if (!$array->items[$i] instanceof ArrayItem) {
                continue;
            }
            $array->items[$i] = $arrayItem;
        }
    }
}
