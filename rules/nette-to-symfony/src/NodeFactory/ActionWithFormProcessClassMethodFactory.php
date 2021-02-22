<?php

declare(strict_types=1);

namespace Rector\NetteToSymfony\NodeFactory;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use Rector\Core\PhpParser\Node\NodeFactory;

final class ActionWithFormProcessClassMethodFactory
{
    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    public function __construct(NodeFactory $nodeFactory)
    {
        $this->nodeFactory = $nodeFactory;
    }

    public function create(string $formTypeClass): ClassMethod
    {
        $classMethod = $this->nodeFactory->createPublicMethod('actionSomeForm');

        $requestVariable = new Variable('request');
        $classMethod->params[] = new Param($requestVariable, null, new FullyQualified(
            'Symfony\Component\HttpFoundation\Request'
        ));
        $classMethod->returnType = new FullyQualified('Symfony\Component\HttpFoundation\Response');

        $formVariable = new Variable('form');

        $assign = $this->createFormInstanceAssign($formTypeClass, $formVariable);
        $classMethod->stmts[] = new Expression($assign);

        $handleRequestMethodCall = new MethodCall($formVariable, 'handleRequest', [new Arg($requestVariable)]);
        $classMethod->stmts[] = new Expression($handleRequestMethodCall);

        $booleanAnd = $this->createFormIsSuccessAndIsValid($formVariable);
        $classMethod->stmts[] = new If_($booleanAnd);

        return $classMethod;
    }

    private function createFormInstanceAssign(string $formTypeClass, Variable $formVariable): Assign
    {
        $classConstFetch = $this->nodeFactory->createClassConstReference($formTypeClass);
        $args = [new Arg($classConstFetch)];
        $createFormMethodCall = new MethodCall(new Variable('this'), 'createForm', $args);

        return new Assign($formVariable, $createFormMethodCall);
    }

    private function createFormIsSuccessAndIsValid(Variable $formVariable): BooleanAnd
    {
        $isSuccessMethodCall = new MethodCall($formVariable, 'isSuccess');
        $isValidMethodCall = new MethodCall($formVariable, 'isValid');

        return new BooleanAnd($isSuccessMethodCall, $isValidMethodCall);
    }
}
