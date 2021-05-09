<?php

declare (strict_types=1);
namespace Rector\Php70\Rector\FuncCall;

use PhpParser\Node;
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
use PHPStan\Reflection\ParametersAcceptor;
use PHPStan\Type\MixedType;
use Rector\Core\PHPStan\Reflection\CallReflectionResolver;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeNestingScope\ParentScopeFinder;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Php70\NodeAnalyzer\VariableNaming;
use Rector\Php70\ValueObject\VariableAssignPair;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://www.php.net/manual/en/migration70.incompatible.php
 *
 * @see \Rector\Tests\Php70\Rector\FuncCall\NonVariableToVariableOnFunctionCallRector\NonVariableToVariableOnFunctionCallRectorTest
 */
final class NonVariableToVariableOnFunctionCallRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var CallReflectionResolver
     */
    private $callReflectionResolver;
    /**
     * @var VariableNaming
     */
    private $variableNaming;
    /**
     * @var ParentScopeFinder
     */
    private $parentScopeFinder;
    public function __construct(\Rector\Core\PHPStan\Reflection\CallReflectionResolver $callReflectionResolver, \Rector\Php70\NodeAnalyzer\VariableNaming $variableNaming, \Rector\NodeNestingScope\ParentScopeFinder $parentScopeFinder)
    {
        $this->callReflectionResolver = $callReflectionResolver;
        $this->variableNaming = $variableNaming;
        $this->parentScopeFinder = $parentScopeFinder;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Transform non variable like arguments to variable where a function or method expects an argument passed by reference', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample('reset(a());', '$a = a(); reset($a);')]);
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
        if (!$currentScope instanceof \PHPStan\Analyser\MutatingScope) {
            return null;
        }
        foreach ($arguments as $key => $argument) {
            $replacements = $this->getReplacementsFor($argument, $currentScope, $scopeNode);
            $current = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CURRENT_STATEMENT);
            $currentStatement = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CURRENT_STATEMENT);
            $this->addNodeBeforeNode($replacements->getAssign(), $current instanceof \PhpParser\Node\Stmt\Return_ ? $current : $currentStatement);
            $node->args[$key]->value = $replacements->getVariable();
            // add variable name to scope, so we prevent duplication of new variable of the same name
            $currentScope = $currentScope->assignExpression($replacements->getVariable(), $currentScope->getType($replacements->getVariable()));
        }
        $scopeNode->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE, $currentScope);
        return $node;
    }
    /**
     * @param FuncCall|MethodCall|StaticCall $node
     *
     * @return Expr[]
     */
    private function getNonVariableArguments(\PhpParser\Node $node) : array
    {
        $arguments = [];
        $parametersAcceptor = $this->callReflectionResolver->resolveParametersAcceptor($this->callReflectionResolver->resolveCall($node), $node);
        if (!$parametersAcceptor instanceof \PHPStan\Reflection\ParametersAcceptor) {
            return [];
        }
        /** @var ParameterReflection $parameterReflection */
        foreach ($parametersAcceptor->getParameters() as $key => $parameterReflection) {
            // omitted optional parameter
            if (!isset($node->args[$key])) {
                continue;
            }
            if ($parameterReflection->passedByReference()->no()) {
                continue;
            }
            $argument = $node->args[$key]->value;
            if ($this->isVariableLikeNode($argument)) {
                continue;
            }
            $arguments[$key] = $argument;
        }
        return $arguments;
    }
    private function getReplacementsFor(\PhpParser\Node\Expr $expr, \PHPStan\Analyser\MutatingScope $mutatingScope, \PhpParser\Node $scopeNode) : \Rector\Php70\ValueObject\VariableAssignPair
    {
        /** @var Assign|AssignOp|AssignRef $expr */
        if ($this->isAssign($expr) && $this->isVariableLikeNode($expr->var)) {
            return new \Rector\Php70\ValueObject\VariableAssignPair($expr->var, $expr);
        }
        $variableName = $this->variableNaming->resolveFromNodeWithScopeCountAndFallbackName($expr, $mutatingScope, 'tmp');
        $variable = new \PhpParser\Node\Expr\Variable($variableName);
        // add a new scope with this variable
        $newVariableAwareScope = $mutatingScope->assignExpression($variable, new \PHPStan\Type\MixedType());
        $scopeNode->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE, $newVariableAwareScope);
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
