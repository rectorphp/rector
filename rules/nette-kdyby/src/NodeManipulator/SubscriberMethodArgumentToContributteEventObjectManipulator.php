<?php

declare(strict_types=1);

namespace Rector\NetteKdyby\NodeManipulator;

use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use Rector\CodingStyle\Naming\ClassNaming;
use Rector\NetteKdyby\ContributeEventClassResolver;

final class SubscriberMethodArgumentToContributteEventObjectManipulator
{
    /**
     * @var ClassNaming
     */
    private $classNaming;

    /**
     * @var ContributeEventClassResolver
     */
    private $contributeEventClassResolver;

    public function __construct(
        ClassNaming $classNaming,
        ContributeEventClassResolver $contributeEventClassResolver
    ) {
        $this->classNaming = $classNaming;
        $this->contributeEventClassResolver = $contributeEventClassResolver;
    }

    /**
     * @param array<string, ClassMethod> $classMethodsByEventClass
     */
    public function change(array $classMethodsByEventClass): void
    {
        foreach ($classMethodsByEventClass as $eventClass => $classMethod) {
            $oldParams = $classMethod->params;

            $this->changeClassParamToEventClass($eventClass, $classMethod);

            // move params
            foreach ($oldParams as $oldParam) {
                $eventGetterToVariableAssign = $this->createEventGetterToVariableMethodCall($eventClass, $oldParam);
                $expression = new Expression($eventGetterToVariableAssign);

                $classMethod->stmts = array_merge([$expression], (array) $classMethod->stmts);
            }
        }
    }

    private function changeClassParamToEventClass(string $eventClass, ClassMethod $classMethod): void
    {
        /** @var ClassMethod $classMethod */
        $paramName = $this->classNaming->getVariableName($eventClass);
        $eventVariable = new Variable($paramName);

        $param = new Param($eventVariable, null, new FullyQualified($eventClass));
        $classMethod->params = [$param];
    }

    private function createEventGetterToVariableMethodCall(string $eventClass, Param $param): Assign
    {
        $paramName = $this->classNaming->getVariableName($eventClass);
        $eventVariable = new Variable($paramName);

        $getterMethod = $this->contributeEventClassResolver->resolveGetterMethodByEventClassAndParam(
            $eventClass,
            $param
        );

        $methodCall = new MethodCall($eventVariable, $getterMethod);

        return new Assign($param->var, $methodCall);
    }
}
