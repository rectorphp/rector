<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony73\NodeAnalyzer;

use PhpParser\Node\Expr;
use PhpParser\Node\Stmt\ClassMethod;
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
    public function __construct(MethodCallFinder $methodCallFinder, ValueResolver $valueResolver)
    {
        $this->methodCallFinder = $methodCallFinder;
        $this->valueResolver = $valueResolver;
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
            $modeExpr = $addArgumentArgs[1]->value ?? null;
            $isArray = \false;
            if ($modeExpr instanceof Expr) {
                $modeValue = $this->valueResolver->getValue($modeExpr);
                // binary check for InputArgument::IS_ARRAY
                $isArray = (bool) ($modeValue & 4);
            }
            $commandArguments[] = new CommandArgument($argumentName, $addArgumentArgs[0]->value, $addArgumentArgs[1]->value ?? null, $addArgumentArgs[2]->value ?? null, $addArgumentArgs[3]->value ?? null, $isArray);
        }
        return $commandArguments;
    }
}
