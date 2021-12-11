<?php

declare (strict_types=1);
namespace Rector\RectorGenerator\NodeFactory;

use PhpParser\Comment\Doc;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\BinaryOp\Coalesce;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use RectorPrefix20211211\Symfony\Component\String\UnicodeString;
final class ConfigureClassMethodFactory
{
    /**
     * @readonly
     * @var \Rector\RectorGenerator\NodeFactory\NodeFactory
     */
    private $nodeFactory;
    public function __construct(\Rector\RectorGenerator\NodeFactory\NodeFactory $nodeFactory)
    {
        $this->nodeFactory = $nodeFactory;
    }
    /**
     * @param array<string, mixed> $ruleConfiguration
     */
    public function create(array $ruleConfiguration) : \PhpParser\Node\Stmt\ClassMethod
    {
        $classMethod = $this->nodeFactory->createPublicMethod('configure');
        $classMethod->returnType = new \PhpParser\Node\Identifier('void');
        $configurationVariable = new \PhpParser\Node\Expr\Variable('configuration');
        $configurationParam = new \PhpParser\Node\Param($configurationVariable);
        $configurationParam->type = new \PhpParser\Node\Identifier('array');
        $classMethod->params[] = $configurationParam;
        $assigns = [];
        foreach (\array_keys($ruleConfiguration) as $constantName) {
            $coalesce = $this->createConstantInConfigurationCoalesce($constantName, $configurationVariable);
            $constantNameString = new \RectorPrefix20211211\Symfony\Component\String\UnicodeString($constantName);
            $propertyName = $constantNameString->lower()->camel()->toString();
            $assign = $this->nodeFactory->createPropertyAssign($propertyName, $coalesce);
            $assigns[] = new \PhpParser\Node\Stmt\Expression($assign);
        }
        $classMethod->stmts = $assigns;
        $paramDoc = <<<'CODE_SAMPLE'
/**
 * @param array<string, mixed> $configuration
 */
CODE_SAMPLE;
        $classMethod->setDocComment(new \PhpParser\Comment\Doc($paramDoc));
        return $classMethod;
    }
    private function createConstantInConfigurationCoalesce(string $constantName, \PhpParser\Node\Expr\Variable $configurationVariable) : \PhpParser\Node\Expr\BinaryOp\Coalesce
    {
        $constantName = \strtoupper($constantName);
        $classConstFetch = new \PhpParser\Node\Expr\ClassConstFetch(new \PhpParser\Node\Name('self'), $constantName);
        $arrayDimFetch = new \PhpParser\Node\Expr\ArrayDimFetch($configurationVariable, $classConstFetch);
        $emptyArray = new \PhpParser\Node\Expr\Array_([]);
        return new \PhpParser\Node\Expr\BinaryOp\Coalesce($arrayDimFetch, $emptyArray);
    }
}
