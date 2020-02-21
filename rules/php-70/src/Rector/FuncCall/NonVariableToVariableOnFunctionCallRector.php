<?php

declare(strict_types=1);

namespace Rector\Php70\Rector\FuncCall;

use function lcfirst;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\AssignOp;
use PhpParser\Node\Expr\AssignRef;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Analyser\MutatingScope;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ParameterReflection;
use Rector\Core\PHPStan\Reflection\CallReflectionResolver;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Php70\ValueObject\VariableAssignPair;

/**
 * @see https://www.php.net/manual/en/migration70.incompatible.php
 *
 * @see \Rector\Php70\Tests\Rector\FuncCall\NonVariableToVariableOnFunctionCallRector\NonVariableToVariableOnFunctionCallRectorTest
 */
final class NonVariableToVariableOnFunctionCallRector extends AbstractRector
{
    /**
     * @var string
     */
    private const DEFAULT_VARIABLE_NAME = 'tmp';

    /**
     * @var CallReflectionResolver
     */
    private $callReflectionResolver;

    public function __construct(CallReflectionResolver $callReflectionResolver)
    {
        $this->callReflectionResolver = $callReflectionResolver;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Transform non variable like arguments to variable where a function or method expects an argument passed by reference',
            [new CodeSample('reset(a());', '$a = a(); reset($a);')]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [FuncCall::class, MethodCall::class, StaticCall::class];
    }

    /**
     * @param FuncCall|MethodCall|StaticCall $node
     */
    public function refactor(Node $node): ?Node
    {
        $scope = $node->getAttribute(AttributeKey::SCOPE);
        if (! $scope instanceof MutatingScope) {
            return null;
        }

        $arguments = $this->getNonVariableArguments($node);

        if ($arguments === []) {
            return null;
        }

        foreach ($arguments as $key => $argument) {
            $replacements = $this->getReplacementsFor($argument, $scope);

            $current = $node->getAttribute(AttributeKey::CURRENT_STATEMENT);
            $this->addNodeBeforeNode($replacements->assign(), $current instanceof Return_ ? $current : $node);
            $node->args[$key]->value = $replacements->variable();

            $scope = $scope->assignExpression($replacements->variable(), $scope->getType($replacements->variable()));
        }

        $node->setAttribute(AttributeKey::SCOPE, $scope);

        return $node;
    }

    /**
     * @param FuncCall|MethodCall|StaticCall $node
     *
     * @return array<int, Expr>
     */
    private function getNonVariableArguments(Node $node): array
    {
        $arguments = [];

        $parametersAcceptor = $this->callReflectionResolver->resolveParametersAcceptor(
            $this->callReflectionResolver->resolveCall($node),
            $node
        );

        if ($parametersAcceptor === null) {
            return [];
        }

        /** @var ParameterReflection $parameter */
        foreach ($parametersAcceptor->getParameters() as $key => $parameter) {
            // omitted optional parameter
            if (! isset($node->args[$key])) {
                continue;
            }

            if ($parameter->passedByReference()->no()) {
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

    private function getReplacementsFor(Expr $expr, Scope $scope): VariableAssignPair
    {
        if (
            (
                $expr instanceof Assign
                || $expr instanceof AssignRef
                || $expr instanceof AssignOp
            )
            && $this->isVariableLikeNode($expr->var)
        ) {
            return new VariableAssignPair($expr->var, $expr);
        }

        $variable = new Variable($this->getVariableNameFor($expr, $scope));

        return new VariableAssignPair($variable, new Assign($variable, $expr));
    }

    private function isVariableLikeNode(Node $node): bool
    {
        return $node instanceof Variable
            || $node instanceof ArrayDimFetch
            || $node instanceof PropertyFetch
            || $node instanceof StaticPropertyFetch;
    }

    private function getVariableNameFor(Expr $expr, Scope $scope): string
    {
        if ($expr instanceof New_ && $expr->class instanceof Name) {
            $name = $this->getShortName($expr->class);
        } elseif ($expr instanceof MethodCall || $expr instanceof StaticCall) {
            $name = $this->getName($expr->name);
        } else {
            $name = $this->getName($expr);
        }

        return lcfirst($this->createCountedValueName($name ?? self::DEFAULT_VARIABLE_NAME, $scope));
    }
}
