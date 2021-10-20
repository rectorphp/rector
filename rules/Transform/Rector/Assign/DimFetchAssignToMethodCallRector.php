<?php

declare (strict_types=1);
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
use RectorPrefix20211020\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Transform\Rector\Assign\DimFetchAssignToMethodCallRector\DimFetchAssignToMethodCallRectorTest
 */
final class DimFetchAssignToMethodCallRector extends \Rector\Core\Rector\AbstractRector implements \Rector\Core\Contract\Rector\ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const DIM_FETCH_ASSIGN_TO_METHOD_CALL = 'dim_fetch_assign_to_method_call';
    /**
     * @var DimFetchAssignToMethodCall[]
     */
    private $dimFetchAssignToMethodCalls = [];
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Change magic array access add to $list[], to explicit $list->addMethod(...)', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(<<<'CODE_SAMPLE'
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
, <<<'CODE_SAMPLE'
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
, [self::DIM_FETCH_ASSIGN_TO_METHOD_CALL => [new \Rector\Transform\ValueObject\DimFetchAssignToMethodCall('Nette\\Application\\Routers\\RouteList', 'Nette\\Application\\Routers\\Route', 'addRoute')]])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\Assign::class];
    }
    /**
     * @param Assign $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$node->var instanceof \PhpParser\Node\Expr\ArrayDimFetch) {
            return null;
        }
        $arrayDimFetch = $node->var;
        if (!$arrayDimFetch->var instanceof \PhpParser\Node\Expr\Variable) {
            return null;
        }
        if (!$node->expr instanceof \PhpParser\Node\Expr\New_) {
            return null;
        }
        $dimFetchAssignToMethodCall = $this->findDimFetchAssignToMethodCall($node);
        if (!$dimFetchAssignToMethodCall instanceof \Rector\Transform\ValueObject\DimFetchAssignToMethodCall) {
            return null;
        }
        return new \PhpParser\Node\Expr\MethodCall($arrayDimFetch->var, $dimFetchAssignToMethodCall->getAddMethod(), $node->expr->args);
    }
    /**
     * @param array<string, DimFetchAssignToMethodCall[]> $configuration
     */
    public function configure(array $configuration) : void
    {
        $dimFetchAssignToMethodCalls = $configuration[self::DIM_FETCH_ASSIGN_TO_METHOD_CALL] ?? [];
        \RectorPrefix20211020\Webmozart\Assert\Assert::allIsInstanceOf($dimFetchAssignToMethodCalls, \Rector\Transform\ValueObject\DimFetchAssignToMethodCall::class);
        $this->dimFetchAssignToMethodCalls = $dimFetchAssignToMethodCalls;
    }
    private function findDimFetchAssignToMethodCall(\PhpParser\Node\Expr\Assign $assign) : ?\Rector\Transform\ValueObject\DimFetchAssignToMethodCall
    {
        /** @var ArrayDimFetch $arrayDimFetch */
        $arrayDimFetch = $assign->var;
        foreach ($this->dimFetchAssignToMethodCalls as $dimFetchAssignToMethodCall) {
            if (!$this->isObjectType($arrayDimFetch->var, $dimFetchAssignToMethodCall->getListObjectType())) {
                continue;
            }
            if (!$this->isObjectType($assign->expr, $dimFetchAssignToMethodCall->getItemObjectType())) {
                continue;
            }
            return $dimFetchAssignToMethodCall;
        }
        return null;
    }
}
