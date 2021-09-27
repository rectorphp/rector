<?php

declare (strict_types=1);
namespace Rector\Php70\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\AssignOp;
use PhpParser\Node\Expr\AssignRef;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Analyser\MutatingScope;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ParameterReflection;
use PHPStan\Type\MixedType;
use Rector\Core\NodeAnalyzer\ArgsAnalyzer;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\Reflection\ReflectionResolver;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\Naming\Naming\VariableNaming;
use Rector\NodeNestingScope\ParentScopeFinder;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Php70\ValueObject\VariableAssignPair;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://www.php.net/manual/en/migration70.incompatible.php
 *
 * @see \Rector\Tests\Php70\Rector\FuncCall\NonVariableToVariableOnFunctionCallRector\NonVariableToVariableOnFunctionCallRectorTest
 */
final class NonVariableToVariableOnFunctionCallRector extends \Rector\Core\Rector\AbstractRector implements \Rector\VersionBonding\Contract\MinPhpVersionInterface
{
    /**
     * @var \Rector\Naming\Naming\VariableNaming
     */
    private $variableNaming;
    /**
     * @var \Rector\NodeNestingScope\ParentScopeFinder
     */
    private $parentScopeFinder;
    /**
     * @var \Rector\Core\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    /**
     * @var \Rector\Core\NodeAnalyzer\ArgsAnalyzer
     */
    private $argsAnalyzer;
    public function __construct(\Rector\Naming\Naming\VariableNaming $variableNaming, \Rector\NodeNestingScope\ParentScopeFinder $parentScopeFinder, \Rector\Core\Reflection\ReflectionResolver $reflectionResolver, \Rector\Core\NodeAnalyzer\ArgsAnalyzer $argsAnalyzer)
    {
        $this->variableNaming = $variableNaming;
        $this->parentScopeFinder = $parentScopeFinder;
        $this->reflectionResolver = $reflectionResolver;
        $this->argsAnalyzer = $argsAnalyzer;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Transform non variable like arguments to variable where a function or method expects an argument passed by reference', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample('reset(a());', '$a = a(); reset($a);')]);
    }
    public function provideMinPhpVersion() : int
    {
        return \Rector\Core\ValueObject\PhpVersionFeature::VARIABLE_ON_FUNC_CALL;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\FuncCall::class, \PhpParser\Node\Expr\MethodCall::class, \PhpParser\Node\Expr\StaticCall::class];
    }
    /**
     * @param FuncCall|MethodCall|StaticCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $arguments = $this->getNonVariableArguments($node);
        if ($arguments === []) {
            return null;
        }
        $scopeNode = $this->parentScopeFinder->find($node);
        if ($scopeNode === null) {
            return null;
        }
        $currentScope = $scopeNode->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        if (!$currentScope instanceof \PHPStan\Analyser\Scope) {
            return null;
        }
        foreach ($arguments as $key => $argument) {
            if (!$node->args[$key] instanceof \PhpParser\Node\Arg) {
                continue;
            }
            $replacements = $this->getReplacementsFor($argument, $currentScope, $scopeNode);
            $current = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CURRENT_STATEMENT);
            $currentStatement = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CURRENT_STATEMENT);
            $this->nodesToAddCollector->addNodeBeforeNode($replacements->getAssign(), $current instanceof \PhpParser\Node\Stmt\Return_ ? $current : $currentStatement);
            $node->args[$key]->value = $replacements->getVariable();
            // add variable name to scope, so we prevent duplication of new variable of the same name
            $currentScope = $currentScope->assignExpression($replacements->getVariable(), $currentScope->getType($replacements->getVariable()));
        }
        $scopeNode->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE, $currentScope);
        return $node;
    }
    /**
     * @return Expr[]
     * @param \PhpParser\Node\Expr\FuncCall|\PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall $call
     */
    private function getNonVariableArguments($call) : array
    {
        $arguments = [];
        $functionLikeReflection = $this->reflectionResolver->resolveFunctionLikeReflectionFromCall($call);
        if ($functionLikeReflection === null) {
            return [];
        }
        foreach ($functionLikeReflection->getVariants() as $parametersAcceptor) {
            /** @var ParameterReflection $parameterReflection */
            foreach ($parametersAcceptor->getParameters() as $key => $parameterReflection) {
                // omitted optional parameter
                if (!$this->argsAnalyzer->isArgInstanceInArgsPosition($call->args, $key)) {
                    continue;
                }
                if ($parameterReflection->passedByReference()->no()) {
                    continue;
                }
                /** @var Arg $arg */
                $arg = $call->args[$key];
                $argument = $arg->value;
                if ($this->isVariableLikeNode($argument)) {
                    continue;
                }
                $arguments[$key] = $argument;
            }
        }
        return $arguments;
    }
    private function getReplacementsFor(\PhpParser\Node\Expr $expr, \PHPStan\Analyser\Scope $scope, \PhpParser\Node $scopeNode) : \Rector\Php70\ValueObject\VariableAssignPair
    {
        /** @var Assign|AssignOp|AssignRef $expr */
        if ($this->isAssign($expr) && $this->isVariableLikeNode($expr->var)) {
            return new \Rector\Php70\ValueObject\VariableAssignPair($expr->var, $expr);
        }
        $variableName = $this->variableNaming->resolveFromNodeWithScopeCountAndFallbackName($expr, $scope, 'tmp');
        $variable = new \PhpParser\Node\Expr\Variable($variableName);
        // add a new scope with this variable
        if ($scope instanceof \PHPStan\Analyser\MutatingScope) {
            $mutatingScope = $scope->assignExpression($variable, new \PHPStan\Type\MixedType());
            $scopeNode->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE, $mutatingScope);
        }
        return new \Rector\Php70\ValueObject\VariableAssignPair($variable, new \PhpParser\Node\Expr\Assign($variable, $expr));
    }
    private function isVariableLikeNode(\PhpParser\Node $node) : bool
    {
        return $node instanceof \PhpParser\Node\Expr\Variable || $node instanceof \PhpParser\Node\Expr\ArrayDimFetch || $node instanceof \PhpParser\Node\Expr\PropertyFetch || $node instanceof \PhpParser\Node\Expr\StaticPropertyFetch;
    }
    private function isAssign(\PhpParser\Node\Expr $expr) : bool
    {
        if ($expr instanceof \PhpParser\Node\Expr\Assign) {
            return \true;
        }
        if ($expr instanceof \PhpParser\Node\Expr\AssignRef) {
            return \true;
        }
        return $expr instanceof \PhpParser\Node\Expr\AssignOp;
    }
}
