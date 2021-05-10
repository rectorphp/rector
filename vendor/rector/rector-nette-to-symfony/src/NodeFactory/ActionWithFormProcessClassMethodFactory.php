<?php

declare (strict_types=1);
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
    public function __construct(\Rector\Core\PhpParser\Node\NodeFactory $nodeFactory)
    {
        $this->nodeFactory = $nodeFactory;
    }
    public function create(string $formTypeClass) : \PhpParser\Node\Stmt\ClassMethod
    {
        $classMethod = $this->nodeFactory->createPublicMethod('actionSomeForm');
        $requestVariable = new \PhpParser\Node\Expr\Variable('request');
        $classMethod->params[] = new \PhpParser\Node\Param($requestVariable, null, new \PhpParser\Node\Name\FullyQualified('Symfony\\Component\\HttpFoundation\\Request'));
        $classMethod->returnType = new \PhpParser\Node\Name\FullyQualified('Symfony\\Component\\HttpFoundation\\Response');
        $formVariable = new \PhpParser\Node\Expr\Variable('form');
        $assign = $this->createFormInstanceAssign($formTypeClass, $formVariable);
        $classMethod->stmts[] = new \PhpParser\Node\Stmt\Expression($assign);
        $handleRequestMethodCall = new \PhpParser\Node\Expr\MethodCall($formVariable, 'handleRequest', [new \PhpParser\Node\Arg($requestVariable)]);
        $classMethod->stmts[] = new \PhpParser\Node\Stmt\Expression($handleRequestMethodCall);
        $booleanAnd = $this->createFormIsSuccessAndIsValid($formVariable);
        $classMethod->stmts[] = new \PhpParser\Node\Stmt\If_($booleanAnd);
        return $classMethod;
    }
    private function createFormInstanceAssign(string $formTypeClass, \PhpParser\Node\Expr\Variable $formVariable) : \PhpParser\Node\Expr\Assign
    {
        $classConstFetch = $this->nodeFactory->createClassConstReference($formTypeClass);
        $args = [new \PhpParser\Node\Arg($classConstFetch)];
        $createFormMethodCall = new \PhpParser\Node\Expr\MethodCall(new \PhpParser\Node\Expr\Variable('this'), 'createForm', $args);
        return new \PhpParser\Node\Expr\Assign($formVariable, $createFormMethodCall);
    }
    private function createFormIsSuccessAndIsValid(\PhpParser\Node\Expr\Variable $formVariable) : \PhpParser\Node\Expr\BinaryOp\BooleanAnd
    {
        $isSuccessMethodCall = new \PhpParser\Node\Expr\MethodCall($formVariable, 'isSuccess');
        $isValidMethodCall = new \PhpParser\Node\Expr\MethodCall($formVariable, 'isValid');
        return new \PhpParser\Node\Expr\BinaryOp\BooleanAnd($isSuccessMethodCall, $isValidMethodCall);
    }
}
