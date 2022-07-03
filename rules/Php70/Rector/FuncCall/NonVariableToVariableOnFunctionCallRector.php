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
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Namespace_;
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
use Rector\PostRector\Collector\NodesToAddCollector;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://www.php.net/manual/en/migration70.incompatible.php
 *
 * @see \Rector\Tests\Php70\Rector\FuncCall\NonVariableToVariableOnFunctionCallRector\NonVariableToVariableOnFunctionCallRectorTest
 */
final class NonVariableToVariableOnFunctionCallRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\Naming\Naming\VariableNaming
     */
    private $variableNaming;
    /**
     * @readonly
     * @var \Rector\NodeNestingScope\ParentScopeFinder
     */
    private $parentScopeFinder;
    /**
     * @readonly
     * @var \Rector\Core\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\ArgsAnalyzer
     */
    private $argsAnalyzer;
    /**
     * @readonly
     * @var \Rector\PostRector\Collector\NodesToAddCollector
     */
    private $nodesToAddCollector;
    public function __construct(VariableNaming $variableNaming, ParentScopeFinder $parentScopeFinder, ReflectionResolver $reflectionResolver, ArgsAnalyzer $argsAnalyzer, NodesToAddCollector $nodesToAddCollector)
    {
        $this->variableNaming = $variableNaming;
        $this->parentScopeFinder = $parentScopeFinder;
        $this->reflectionResolver = $reflectionResolver;
        $this->argsAnalyzer = $argsAnalyzer;
        $this->nodesToAddCollector = $nodesToAddCollector;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Transform non variable like arguments to variable where a function or method expects an argument passed by reference', [new CodeSample('reset(a());', '$a = a(); reset($a);')]);
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::VARIABLE_ON_FUNC_CALL;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [FuncCall::class, MethodCall::class, StaticCall::class];
    }
    /**
     * @param FuncCall|MethodCall|StaticCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        $arguments = $this->getNonVariableArguments($node);
        if ($arguments === []) {
            return null;
        }
        $scopeNode = $this->parentScopeFinder->find($node);
        if (!$scopeNode instanceof Node) {
            return null;
        }
        $currentScope = $scopeNode->getAttribute(AttributeKey::SCOPE);
        if (!$currentScope instanceof MutatingScope) {
            return null;
        }
        foreach ($arguments as $key => $argument) {
            if (!$node->args[$key] instanceof Arg) {
                continue;
            }
            $replacements = $this->getReplacementsFor($argument, $currentScope, $scopeNode);
            $currentStmt = $this->betterNodeFinder->resolveCurrentStatement($node);
            if (!$currentStmt instanceof Stmt) {
                continue;
            }
            $this->nodesToAddCollector->addNodeBeforeNode($replacements->getAssign(), $currentStmt);
            $node->args[$key]->value = $replacements->getVariable();
            // add variable name to scope, so we prevent duplication of new variable of the same name
            $currentScope = $currentScope->assignExpression($replacements->getVariable(), $currentScope->getType($replacements->getVariable()));
        }
        $scopeNode->setAttribute(AttributeKey::SCOPE, $currentScope);
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
    /**
     * @param \PhpParser\Node\Expr\Closure|\PhpParser\Node\Stmt\Class_|\PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Stmt\Namespace_ $scopeNode
     */
    private function getReplacementsFor(Expr $expr, MutatingScope $scope, $scopeNode) : VariableAssignPair
    {
        if ($this->isAssign($expr)) {
            /** @var Assign|AssignRef|AssignOp $expr */
            if ($this->isVariableLikeNode($expr->var)) {
                return new VariableAssignPair($expr->var, $expr);
            }
        }
        $variableName = $this->variableNaming->resolveFromNodeWithScopeCountAndFallbackName($expr, $scope, 'tmp');
        $variable = new Variable($variableName);
        // add a new scope with this variable
        $mutatingScope = $scope->assignExpression($variable, new MixedType());
        $scopeNode->setAttribute(AttributeKey::SCOPE, $mutatingScope);
        return new VariableAssignPair($variable, new Assign($variable, $expr));
    }
    private function isVariableLikeNode(Expr $expr) : bool
    {
        return $expr instanceof Variable || $expr instanceof ArrayDimFetch || $expr instanceof PropertyFetch || $expr instanceof StaticPropertyFetch;
    }
    private function isAssign(Expr $expr) : bool
    {
        if ($expr instanceof Assign) {
            return \true;
        }
        if ($expr instanceof AssignRef) {
            return \true;
        }
        return $expr instanceof AssignOp;
    }
}
