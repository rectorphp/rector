<?php

declare(strict_types=1);

namespace Rector\Nette\Rector\Assign;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Nette\Tests\Rector\Assign\ArrayAccessAddRouteToAddRouteMethodCallRector\ArrayAccessAddRouteToAddRouteMethodCallRectorTest
 */
final class ArrayAccessAddRouteToAddRouteMethodCallRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Change magic array access add Route, to explicit $routeList->addRoute(...) method',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;

class RouterFactory
{
    public static function createRouter()
    {
        $routeList = new RouteList();
        $routeList[] = new Route('<module>/<presenter>/<action>[/<id>]', 'Homepage:default');
        return $routeList;
    }
}
CODE_SAMPLE
,
                    <<<'CODE_SAMPLE'
use Nette\Application\Routers\RouteList;

class RouterFactory
{
    public static function createRouter()
    {
        $routeList = new RouteList();
        $routeList->addRoute('<module>/<presenter>/<action>[/<id>]', 'Homepage:default');
        return $routeList;
    }
}
CODE_SAMPLE
                ),

            ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Assign::class];
    }

    /**
     * @param Assign $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isAssignOfRouteToRouteListDimFetch($node)) {
            return null;
        }

        if (! $node->expr instanceof Node\Expr\New_) {
            return null;
        }

        /** @var ArrayDimFetch $arrayDimFetch */
        $arrayDimFetch = $node->var;

        return new MethodCall($arrayDimFetch->var, 'addRoute', $node->expr->args);
    }

    private function isAssignOfRouteToRouteListDimFetch(Assign $assign): bool
    {
        if (! $assign->var instanceof ArrayDimFetch) {
            return false;
        }

        if (! $this->isObjectType($assign->expr, 'Nette\Application\Routers\Route')) {
            return false;
        }

        $arrayDimFetch = $assign->var;
        if (! $arrayDimFetch->var instanceof Variable) {
            return false;
        }

        return $this->isObjectType($arrayDimFetch->var, 'Nette\Application\Routers\RouteList');
    }
}
