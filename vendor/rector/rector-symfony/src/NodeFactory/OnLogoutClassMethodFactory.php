<?php

declare (strict_types=1);
namespace Rector\Symfony\NodeFactory;

use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\NodeAnalyzer\ParamAnalyzer;
use Rector\NodeNameResolver\NodeNameResolver;
final class OnLogoutClassMethodFactory
{
    /**
     * @var array<string, string>
     */
    private const PARAMETER_TO_GETTER_NAMES = ['request' => 'getRequest', 'response' => 'getResponse', 'token' => 'getToken'];
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\Symfony\NodeFactory\BareLogoutClassMethodFactory
     */
    private $bareLogoutClassMethodFactory;
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\ParamAnalyzer
     */
    private $paramAnalyzer;
    public function __construct(NodeNameResolver $nodeNameResolver, \Rector\Symfony\NodeFactory\BareLogoutClassMethodFactory $bareLogoutClassMethodFactory, ParamAnalyzer $paramAnalyzer)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->bareLogoutClassMethodFactory = $bareLogoutClassMethodFactory;
        $this->paramAnalyzer = $paramAnalyzer;
    }
    public function createFromLogoutClassMethod(ClassMethod $logoutClassMethod) : ClassMethod
    {
        $classMethod = $this->bareLogoutClassMethodFactory->create();
        $assignStmts = $this->createAssignStmtFromOldClassMethod($logoutClassMethod);
        $classMethod->stmts = \array_merge($assignStmts, (array) $logoutClassMethod->stmts);
        return $classMethod;
    }
    /**
     * @return Stmt[]
     */
    private function createAssignStmtFromOldClassMethod(ClassMethod $onLogoutSuccessClassMethod) : array
    {
        $usedParams = $this->resolveUsedParams($onLogoutSuccessClassMethod);
        return $this->createAssignStmts($usedParams);
    }
    /**
     * @return Param[]
     */
    private function resolveUsedParams(ClassMethod $logoutClassMethod) : array
    {
        $usedParams = [];
        foreach ($logoutClassMethod->params as $oldParam) {
            if (!$this->paramAnalyzer->isParamUsedInClassMethod($logoutClassMethod, $oldParam)) {
                continue;
            }
            $usedParams[] = $oldParam;
        }
        return $usedParams;
    }
    /**
     * @param Param[] $params
     * @return Expression[]
     */
    private function createAssignStmts(array $params) : array
    {
        $logoutEventVariable = new Variable('logoutEvent');
        $assignStmts = [];
        foreach ($params as $param) {
            foreach (self::PARAMETER_TO_GETTER_NAMES as $parameterName => $getterName) {
                if (!$this->nodeNameResolver->isName($param, $parameterName)) {
                    continue;
                }
                $assign = new Assign($param->var, new MethodCall($logoutEventVariable, $getterName));
                $assignStmts[] = new Expression($assign);
            }
        }
        return $assignStmts;
    }
}
