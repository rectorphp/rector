<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony73\NodeAnalyzer;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\Type;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\Symfony\Symfony73\NodeFinder\MethodCallFinder;
use Rector\Symfony\Symfony73\ValueObject\CommandArgument;
final class CommandArgumentsResolver
{
    /**
     * @readonly
     */
    private MethodCallFinder $methodCallFinder;
    /**
     * @readonly
     */
    private ValueResolver $valueResolver;
    /**
     * @readonly
     */
    private NodeTypeResolver $nodeTypeResolver;
    public function __construct(MethodCallFinder $methodCallFinder, ValueResolver $valueResolver, NodeTypeResolver $nodeTypeResolver)
    {
        $this->methodCallFinder = $methodCallFinder;
        $this->valueResolver = $valueResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
    }
    /**
     * @return CommandArgument[]
     */
    public function resolve(ClassMethod $configureClassMethod): array
    {
        $addArgumentMethodCalls = $this->methodCallFinder->find($configureClassMethod, 'addArgument');
        $commandArguments = [];
        foreach ($addArgumentMethodCalls as $addArgumentMethodCall) {
            $addArgumentArgs = $addArgumentMethodCall->getArgs();
            $argumentName = $this->valueResolver->getValue($addArgumentArgs[0]->value);
            $isArray = $this->isArrayMode($addArgumentArgs);
            $commandArguments[] = new CommandArgument($argumentName, $addArgumentArgs[0]->value, $addArgumentArgs[1]->value ?? null, $addArgumentArgs[2]->value ?? null, $addArgumentArgs[3]->value ?? null, $isArray, $this->resolveDefaultType($addArgumentArgs));
        }
        return $commandArguments;
    }
    /**
     * @param Arg[] $args
     */
    private function resolveDefaultType(array $args): ?Type
    {
        $defaultArg = $args[3] ?? null;
        if (!$defaultArg instanceof Arg) {
            return null;
        }
        return $this->nodeTypeResolver->getType($defaultArg->value);
    }
    /**
     * @param Arg[] $args
     */
    private function isArrayMode(array $args): bool
    {
        $modeExpr = $args[1]->value ?? null;
        if (!$modeExpr instanceof Expr) {
            return \false;
        }
        $modeValue = $this->valueResolver->getValue($modeExpr);
        // binary check for InputArgument::IS_ARRAY
        return (bool) ($modeValue & 4);
    }
}
