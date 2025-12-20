<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony73\NodeAnalyzer;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
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
            $nameArg = $addOptionMethodCall->getArg('name', 0);
            if (!$nameArg instanceof Arg) {
                continue;
            }
            $optionName = $this->valueResolver->getValue($nameArg->value);
            $isImplicitBoolean = $this->isImplicitBoolean($addOptionMethodCall);
            $commandOptions[] = new CommandOption($optionName, $nameArg->value, ($nullsafeVariable1 = $addOptionMethodCall->getArg('shortcut', 1)) ? $nullsafeVariable1->value : null, ($nullsafeVariable2 = $addOptionMethodCall->getArg('mode', 2)) ? $nullsafeVariable2->value : null, ($nullsafeVariable3 = $addOptionMethodCall->getArg('description', 3)) ? $nullsafeVariable3->value : null, ($nullsafeVariable4 = $addOptionMethodCall->getArg('default', 4)) ? $nullsafeVariable4->value : null, $this->isArrayMode($addOptionMethodCall), $isImplicitBoolean, $this->resolveDefaultType($addOptionMethodCall));
        }
        return $commandOptions;
    }
    private function resolveDefaultType(MethodCall $methodCall): ?Type
    {
        $defaultExpr = ($nullsafeVariable5 = $methodCall->getArg('default', 4)) ? $nullsafeVariable5->value : null;
        if (!$defaultExpr instanceof Expr) {
            return null;
        }
        return $this->nodeTypeResolver->getType($defaultExpr);
    }
    private function isArrayMode(MethodCall $methodCall): bool
    {
        $modeExpr = ($nullsafeVariable6 = $methodCall->getArg('mode', 2)) ? $nullsafeVariable6->value : null;
        if (!$modeExpr instanceof Expr) {
            return \false;
        }
        $modeValue = $this->valueResolver->getValue($modeExpr);
        // binary check for InputOption::VALUE_IS_ARRAY
        return (bool) ($modeValue & 8);
    }
    private function isImplicitBoolean(MethodCall $methodCall): bool
    {
        $modeExpr = ($nullsafeVariable7 = $methodCall->getArg('mode', 2)) ? $nullsafeVariable7->value : null;
        if (!$modeExpr instanceof Expr) {
            return \false;
        }
        $modeValue = $this->valueResolver->getValue($modeExpr);
        // binary check for InputOption::VALUE_NONE
        return (bool) ($modeValue & 1);
    }
}
