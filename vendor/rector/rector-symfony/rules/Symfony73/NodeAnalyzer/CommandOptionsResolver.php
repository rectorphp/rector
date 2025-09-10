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
use Rector\Symfony\Symfony73\ValueObject\CommandOption;
final class CommandOptionsResolver
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
     * @return CommandOption[]
     */
    public function resolve(ClassMethod $configureClassMethod): array
    {
        $addOptionMethodCalls = $this->methodCallFinder->find($configureClassMethod, 'addOption');
        $commandOptions = [];
        foreach ($addOptionMethodCalls as $addOptionMethodCall) {
            $addOptionArgs = $addOptionMethodCall->getArgs();
            $optionName = $this->valueResolver->getValue($addOptionArgs[0]->value);
            $isImplicitBoolean = $this->isImplicitBoolean($addOptionArgs);
            $commandOptions[] = new CommandOption($optionName, $addOptionArgs[0]->value, $addOptionArgs[1]->value ?? null, $addOptionArgs[2]->value ?? null, $addOptionArgs[3]->value ?? null, $addOptionArgs[4]->value ?? null, $this->isArrayMode($addOptionArgs), $isImplicitBoolean, $this->resolveDefaultType($addOptionArgs));
        }
        return $commandOptions;
    }
    /**
     * @param Arg[] $args
     */
    private function resolveDefaultType(array $args): ?Type
    {
        $defaultArg = $args[4] ?? null;
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
        $modeExpr = $args[2]->value ?? null;
        if (!$modeExpr instanceof Expr) {
            return \false;
        }
        $modeValue = $this->valueResolver->getValue($modeExpr);
        // binary check for InputOption::VALUE_IS_ARRAY
        return (bool) ($modeValue & 8);
    }
    /**
     * @param Arg[] $args
     */
    private function isImplicitBoolean(array $args): bool
    {
        $modeExpr = $args[2]->value ?? null;
        if (!$modeExpr instanceof Expr) {
            return \false;
        }
        $modeValue = $this->valueResolver->getValue($modeExpr);
        // binary check for InputOption::VALUE_NONE
        return (bool) ($modeValue & 1);
    }
}
