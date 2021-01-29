<?php

declare(strict_types=1);

namespace Rector\Transform\Rector\Assign;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Variable;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Transform\ValueObject\DimFetchAssignToMethodCall;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Webmozart\Assert\Assert;

/**
 * @see \Rector\Transform\Tests\Rector\Assign\DimFetchAssignToMethodCallRector\DimFetchAssignToMethodCallRectorTest
 */
final class DimFetchAssignToMethodCallRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const DIM_FETCH_ASSIGN_TO_METHOD_CALL = 'dim_fetch_assign_to_method_call';

    /**
     * @var DimFetchAssignToMethodCall[]
     */
    private $dimFetchAssignToMethodCalls = [];

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Change magic array access add to $list[], to explicit $list->addMethod(...)',
            [
                new ConfiguredCodeSample(
                    <<<'CODE_SAMPLE'
use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;

class RouterFactory
{
    public static function createRouter()
    {
        $routeList = new RouteList();
        $routeList[] = new Route('...');
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
        $routeList->addRoute('...');
    }
}
CODE_SAMPLE
,
                    [
                        self::DIM_FETCH_ASSIGN_TO_METHOD_CALL => [
                            new DimFetchAssignToMethodCall(
                                'Nette\Application\Routers\RouteList',
                                'Nette\Application\Routers\Route',
                                'addRoute'
                            ),
                        ],
                    ]
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
        if (! $node->var instanceof ArrayDimFetch) {
            return null;
        }

        $arrayDimFetch = $node->var;
        if (! $arrayDimFetch->var instanceof Variable) {
            return null;
        }

        if (! $node->expr instanceof New_) {
            return null;
        }

        $dimFetchAssignToMethodCall = $this->findDimFetchAssignToMethodCall($node);
        if (! $dimFetchAssignToMethodCall instanceof DimFetchAssignToMethodCall) {
            return null;
        }

        return new MethodCall($arrayDimFetch->var, $dimFetchAssignToMethodCall->getAddMethod(), $node->expr->args);
    }

    public function configure(array $configuration): void
    {
        $dimFetchAssignToMethodCalls = $configuration[self::DIM_FETCH_ASSIGN_TO_METHOD_CALL] ?? [];
        Assert::allIsInstanceOf($dimFetchAssignToMethodCalls, DimFetchAssignToMethodCall::class);
        $this->dimFetchAssignToMethodCalls = $dimFetchAssignToMethodCalls;
    }

    private function findDimFetchAssignToMethodCall(Assign $assign): ?DimFetchAssignToMethodCall
    {
        /** @var ArrayDimFetch $arrayDimFetch */
        $arrayDimFetch = $assign->var;

        foreach ($this->dimFetchAssignToMethodCalls as $dimFetchAssignToMethodCall) {
            if ($this->isObjectType(
                $arrayDimFetch->var,
                $dimFetchAssignToMethodCall->getListClass()
            ) && $this->isObjectType($assign->expr, $dimFetchAssignToMethodCall->getItemClass())) {
                return $dimFetchAssignToMethodCall;
            }
        }
        return null;
    }
}
